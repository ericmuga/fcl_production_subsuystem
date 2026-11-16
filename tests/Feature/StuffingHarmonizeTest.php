<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BcPlans\BcStaging;
use App\Services\BcPlans\ExecutionPublisher;
use App\Services\Stuffing\PlanMatcher;
use App\Services\Stuffing\ProductionDataWriter;
use App\Services\Stuffing\StuffingHarmonizer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Harmonizing stuffing orders with the dispatch weight: stuffing measures the input, the transfer to
 * dispatch measures the output, the lines between are rebuilt from both.
 *
 * Recipes used: ZZR1 stuffs 90 meat + 2 casing + 8 spice into 100 sausage; ZZR2 packs 100 sausage + 5
 * packaging into 98 packed.
 */
class StuffingHarmonizeTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private int $nextId = 1;
    public array $written = [];
    public array $staged = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['production_orders.recipe_table' => 'recipe_data_draft', 'production_orders.harmonize' => 'review',
            'production_orders.target' => 'production_data', 'production_orders.harmonize_output_from' => '2055']);
        $this->user = User::firstOrCreate(['username' => 'zz_test_harmonize'], ['email' => 'zz_test_harmonize@example.invalid', 'role' => 'user']);

        $recipe = fn($recipe, $process, $out, $batch, $oloc, $in, $qty, $iloc) => [
            'process' => $process, 'output_item' => $out, 'recipe' => $recipe, 'batch_size' => $batch, 'output_item_location' => $oloc,
            'input_item' => $in, 'input_item_qt_per' => $qty, 'input_item_location' => $iloc, 'output_item_uom' => 'KG', 'input_item_uom' => 'KG',
        ];
        DB::table('recipe_data_draft')->insert([
            $recipe('ZZR1', 'Stuffing', 'ZZS', 100, '2055', 'ZZM', 90, '2005'),
            $recipe('ZZR1', 'Stuffing', 'ZZS', 100, '2055', 'ZZC', 2, '2035'),
            $recipe('ZZR1', 'Stuffing', 'ZZS', 100, '2055', 'ZZP', 8, '2005'),
            $recipe('ZZR2', 'Packing', 'ZZJ', 98, '3535', 'ZZS', 100, '2055'),
            $recipe('ZZR2', 'Packing', 'ZZJ', 98, '3535', 'ZZH', 5, '2035'),
        ]);

        // BC is not written from tests: record what would be.
        $test = $this;
        $this->app->instance(ProductionDataWriter::class, new class($test) extends ProductionDataWriter {
            private $t;
            public function __construct($t) { $this->t = $t; }
            public function write(array $rows, string $username): string
            {
                $this->t->written = array_merge($this->t->written, $rows);
                DB::table('generated_production_orders')->whereIn('id', array_map(fn($r) => ((array) $r)['id'], $rows))->update(['production_data_at' => now()]);
                return ', ' . count($rows) . ' written to ProductionData';
            }
        });
    }

    private function fakePlanRoute(): void
    {
        $test = $this;
        $this->app->instance(ExecutionPublisher::class, new class($test) extends ExecutionPublisher {
            private $t;
            public function __construct($t) { $this->t = $t; }
            public function publishNow(array $orderNos): array
            {
                $this->t->staged = array_merge($this->t->staged, $orderNos);
                DB::table('generated_production_orders')->whereIn('production_order_no', $orderNos)->update(['bc_staged_at' => now(), 'bc_status' => 'pending']);
                return ['candidates' => count($orderNos), 'staged' => count($orderNos), 'duplicate' => 0, 'rejected' => 0, 'errors' => 0, 'statuses' => 0];
            }
        });
    }

    private function plan(string $no, string $item, string $loc, int $dayOffset, float $qty, string $status = 'released'): void
    {
        DB::table('bc_plan_orders')->insert([
            'plan_no' => $no, 'item_no' => $item, 'description' => 'Test', 'location_code' => $loc, 'plan_date' => today()->addDays($dayOffset)->toDateString(),
            'uom' => 'KG', 'batch_uom' => 'KG', 'batch_size_base' => 100, 'planned_qty' => $qty, 'executed_qty' => 0, 'remaining_qty' => $qty,
            'status' => $status, 'section' => 'Sausage', 'sections' => 'Sausage', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function planOf(string $order): ?string
    {
        return DB::table('generated_production_orders')->where('production_order_no', $order)->where('line_type', 'output')->value('plan_order_no');
    }

    /** One weighing as the generator writes it, on recipe yields from the stuffing weight. */
    private function weighing(int $transferId, float $net, string $batch = 'ZZB1'): void
    {
        $k = $net / 90;
        $common = ['weighed_item' => 'ZZM', 'packed_item' => 'ZZJ', 'batch_no' => $batch, 'net_weight' => $net, 'idt_transfer_id' => $transferId,
            'user_id' => $this->user->id, 'transaction_date' => today()->toDateString(), 'created_at' => now(), 'updated_at' => now(), 'uom' => 'KG'];
        $line = fn($order, $step, $process, $recipe, $no, $type, $item, $qty, $loc) => $common + [
            'production_order_no' => $order, 'step' => $step, 'process' => $process, 'recipe' => $recipe, 'routing' => $process,
            'line_no' => $no, 'line_type' => $type, 'item_no' => $item, 'quantity' => round($qty, 5), 'location_code' => $loc, 'external_document_no' => 'ZZR2',
            'transaction_id' => 'GPO:' . $order,
        ];
        DB::table('generated_production_orders')->insert([
            $line("ZZP19_{$transferId}", 1, 'Stuffing', 'ZZR1', 1000, 'output', 'ZZS', 100 * $k, '2055'),
            $line("ZZP19_{$transferId}", 1, 'Stuffing', 'ZZR1', 2000, 'consumption', 'ZZM', 90 * $k, '2005'),
            $line("ZZP19_{$transferId}", 1, 'Stuffing', 'ZZR1', 3000, 'consumption', 'ZZC', 2 * $k, ''),   // blank: refreshed from the recipe
            $line("ZZP19_{$transferId}", 1, 'Stuffing', 'ZZR1', 4000, 'consumption', 'ZZP', 8 * $k, '2005'),
            $line("ZZP20_{$transferId}", 2, 'Packing', 'ZZR2', 1000, 'output', 'ZZJ', 98 * $k, '3535'),
            $line("ZZP20_{$transferId}", 2, 'Packing', 'ZZR2', 2000, 'consumption', 'ZZS', 100 * $k, '2055'),
            $line("ZZP20_{$transferId}", 2, 'Packing', 'ZZR2', 3000, 'consumption', 'ZZH', 5 * $k, '2035'),
        ]);
    }

    private function dispatch(float $kg, string $batch = 'ZZB1'): void
    {
        DB::table('idt_transfers')->insert(['product_code' => 'ZZJ', 'location_code' => '3535', 'total_weight' => $kg, 'transfer_from' => '2055',
            'with_variance' => 0, 'transfer_type' => 0, 'batch_no' => $batch, 'user_id' => $this->user->id, 'filter1' => 0, 'created_at' => now(), 'updated_at' => now()]);
    }

    private function qty(string $order, int $lineNo): float
    {
        return (float) DB::table('generated_production_orders')->where('production_order_no', $order)->where('line_no', $lineNo)->value('quantity');
    }

    private function harmonizer(): StuffingHarmonizer
    {
        return app(StuffingHarmonizer::class);
    }

    public function test_output_at_last_puts_the_dispatch_weight_on_packing_and_scales_its_packaging()
    {
        $this->weighing(900001, 90);
        $this->dispatch(95);

        $r = $this->harmonizer()->apply('ZZB1', 'ZZJ', 'output_at_last', false, 'tester');

        $this->assertSame([], $r['errors']);
        $this->assertEqualsWithDelta(100, $this->qty('ZZP19_900001', 1000), 0.0001);     // stuffing on recipe
        $this->assertEqualsWithDelta(90, $this->qty('ZZP19_900001', 2000), 0.0001);      // measured input kept
        $this->assertEqualsWithDelta(95, $this->qty('ZZP20_900001', 1000), 0.0001);      // measured output
        $this->assertEqualsWithDelta(100, $this->qty('ZZP20_900001', 2000), 0.0001);     // packing takes what stuffing made
        $this->assertEqualsWithDelta(5 * 95 / 98, $this->qty('ZZP20_900001', 3000), 0.0001);
        $this->assertSame('2035', DB::table('generated_production_orders')->where('production_order_no', 'ZZP19_900001')->where('line_no', 3000)->value('location_code'));
        $this->assertEqualsWithDelta(98, (float) DB::table('generated_production_orders')->where('production_order_no', 'ZZP20_900001')->where('line_no', 1000)->value('recipe_quantity'), 0.0001);
        $this->assertSame([], $this->written, 'save only: nothing released');
    }

    public function test_output_at_first_works_back_from_dispatch_and_keeps_the_measured_input()
    {
        $this->weighing(900001, 90);
        $this->dispatch(95);

        $this->harmonizer()->apply('ZZB1', 'ZZJ', 'output_at_first', false, 'tester');

        $this->assertEqualsWithDelta(95, $this->qty('ZZP20_900001', 1000), 0.0001);
        $this->assertEqualsWithDelta(100 * 95 / 98, $this->qty('ZZP20_900001', 2000), 0.0001);
        $this->assertEqualsWithDelta(100 * 95 / 98, $this->qty('ZZP19_900001', 1000), 0.0001);   // stuffing output = what packing used
        $this->assertEqualsWithDelta(90, $this->qty('ZZP19_900001', 2000), 0.0001);
        $this->assertEqualsWithDelta(2, $this->qty('ZZP19_900001', 3000), 0.0001);
    }

    public function test_dispatch_weight_is_shared_over_the_batch_weighings_by_expected_output()
    {
        $this->weighing(900001, 90);
        $this->weighing(900002, 45);
        $this->dispatch(100);
        $this->dispatch(40);

        $this->harmonizer()->apply('ZZB1', 'ZZJ', 'output_at_last', false, 'tester');

        $this->assertEqualsWithDelta(140 * 98 / 147, $this->qty('ZZP20_900001', 1000), 0.0001);
        $this->assertEqualsWithDelta(140 * 49 / 147, $this->qty('ZZP20_900002', 1000), 0.0001);
    }

    public function test_recipe_mode_restores_the_generated_quantities_and_nothing_dispatched_is_reported()
    {
        $this->weighing(900001, 90);
        $plan = $this->harmonizer()->plan('ZZB1', 'ZZJ', 'output_at_last');
        $this->assertNotEmpty($plan['errors']);
        $this->assertEqualsWithDelta(98, $plan['lines']->firstWhere('line_no', 1000)->new_quantity ?? 0, 100);

        $this->dispatch(95);
        $this->harmonizer()->apply('ZZB1', 'ZZJ', 'output_at_last', false, 'tester');
        $this->harmonizer()->apply('ZZB1', 'ZZJ', 'recipe', false, 'tester');
        $this->assertEqualsWithDelta(98, $this->qty('ZZP20_900001', 1000), 0.0001);
        $this->assertEqualsWithDelta(5, $this->qty('ZZP20_900001', 3000), 0.0001);
    }

    public function test_release_writes_once_and_released_lines_are_not_changed_again()
    {
        $this->weighing(900001, 90);
        $this->dispatch(95);

        $r = $this->harmonizer()->apply('ZZB1', 'ZZJ', 'output_at_last', true, 'tester');
        $this->assertCount(7, $this->written);
        $this->assertStringContainsString('7 written', $r['written']);

        $this->dispatch(10);   // a late transfer
        $this->harmonizer()->apply('ZZB1', 'ZZJ', 'output_at_last', true, 'tester');
        $this->assertCount(7, $this->written, 'nothing written twice');
        $this->assertEqualsWithDelta(95, $this->qty('ZZP20_900001', 1000), 0.0001, 'released line kept');

        $listed = $this->harmonizer()->batches(today()->toDateString(), today()->toDateString(), 'ZZB1', 'all')->first();
        $this->assertSame('released', $listed->state);
        $this->assertEqualsWithDelta(105, $listed->dispatched_kg, 0.0001);
    }

    public function test_screen_lists_previews_and_applies()
    {
        $this->weighing(900001, 90);
        $this->dispatch(95);

        $this->actingAs($this->user)->get(route('stuffing_harmonize', ['q' => 'ZZB1', 'batch' => 'ZZB1', 'item' => 'ZZJ', 'mode' => 'output_at_last']))
            ->assertOk()->assertSee('ZZB1')->assertSee('Save and release to BC')->assertSee('95.000');

        $this->actingAs($this->user)->post(route('stuffing_harmonize_apply'), ['batch' => 'ZZB1', 'item' => 'ZZJ', 'mode' => 'output_at_last', 'release' => 1])
            ->assertRedirect();
        $this->assertCount(7, $this->written);
    }

    public function test_review_mode_holds_new_orders_back_from_production_data()
    {
        $writer = new ProductionDataWriter();
        $this->assertTrue($writer->holdsForHarmonization());
        config(['production_orders.harmonize' => 'off']);
        $this->assertFalse($writer->holdsForHarmonization());
    }

    public function test_orders_match_plans_on_their_own_output_location_and_date()
    {
        $this->weighing(900001, 90);
        $this->plan('ZZPL-J', 'ZZJ', '3535', 0, 500);              // packed item, same day
        $this->plan('ZZPL-J6', 'ZZJ', '3600', 0, 500);             // other location: manual only
        $this->plan('ZZPL-S', 'ZZS', '2055', -1, 300, 'open');     // stuffed item, the day before
        $this->plan('ZZPL-SX', 'ZZS', '2055', 0, 300, 'closed');   // closed: never

        $r = app(PlanMatcher::class)->autoMatch(['ZZP19_900001', 'ZZP20_900001']);

        $this->assertSame(2, $r['matched']);
        $this->assertSame('ZZPL-J', $this->planOf('ZZP20_900001'));
        $this->assertSame('ZZPL-S', $this->planOf('ZZP19_900001'));
        $this->assertSame(3, DB::table('generated_production_orders')->where('production_order_no', 'ZZP20_900001')
            ->where('plan_order_no', 'ZZPL-J')->where('plan_matched_by', 'auto')->count(), 'every line of the order carries the plan');
    }

    public function test_a_plan_picked_by_hand_is_kept_and_sent_orders_cannot_change()
    {
        $this->weighing(900001, 90);
        $this->dispatch(95);
        $this->plan('ZZPL-J', 'ZZJ', '3535', 0, 500);
        $this->plan('ZZPL-J6', 'ZZJ', '3600', 0, 500);

        $this->actingAs($this->user)->post(route('stuffing_harmonize_plan'), ['batch' => 'ZZB1', 'item' => 'ZZJ', 'order' => 'ZZP20_900001', 'plan' => 'ZZPL-J6'])
            ->assertRedirect();
        $this->assertSame('ZZPL-J6', $this->planOf('ZZP20_900001'));

        $this->harmonizer()->apply('ZZB1', 'ZZJ', 'output_at_last', false, 'tester');   // matching runs again
        $this->assertSame('ZZPL-J6', $this->planOf('ZZP20_900001'), 'manual choice kept');

        $this->actingAs($this->user)->post(route('stuffing_harmonize_plan'), ['batch' => 'ZZB1', 'item' => 'ZZJ', 'order' => 'ZZP20_900001', 'plan' => 'auto']);
        $this->assertSame('ZZPL-J', $this->planOf('ZZP20_900001'), 'back to automatic');

        $this->harmonizer()->apply('ZZB1', 'ZZJ', 'output_at_last', true, 'tester');
        $this->expectException(\RuntimeException::class);
        app(PlanMatcher::class)->setManual('ZZP20_900001', 'ZZPL-J6');
    }

    public function test_release_sends_each_order_by_one_route()
    {
        config(['production_orders.planned_route' => 'plan']);
        $this->fakePlanRoute();
        $this->weighing(900001, 90);
        $this->dispatch(95);
        $this->plan('ZZPL-J', 'ZZJ', '3535', 0, 500);              // only the packing order has a plan

        $r = $this->harmonizer()->apply('ZZB1', 'ZZJ', 'output_at_last', true, 'tester');

        $this->assertSame(['ZZP20_900001'], $this->staged, 'planned order over the plan route');
        $this->assertSame(['ZZP19_900001'], collect($this->written)->pluck('production_order_no')->unique()->values()->all(), 'unplanned to ProductionData');
        $this->assertSame('plan', DB::table('generated_production_orders')->where('production_order_no', 'ZZP20_900001')->value('released_to'));
        $this->assertSame('production_data', DB::table('generated_production_orders')->where('production_order_no', 'ZZP19_900001')->value('released_to'));
        $this->assertStringContainsString('1 of 1 order(s) staged', $r['written']);
    }

    public function test_scheduled_plan_route_waits_for_release_in_review_mode()
    {
        $this->weighing(900001, 90);
        $this->plan('ZZPL-J', 'ZZJ', '3535', 0, 500);
        app(PlanMatcher::class)->autoMatch(['ZZP20_900001']);
        config(['bc_plan.push_unplanned' => false]);
        $publisher = new ExecutionPublisher($this->createMock(BcStaging::class));

        $this->assertSame(0, $publisher->run(500, true, ['ZZP20_900001'])['candidates'], 'held until released');

        DB::table('generated_production_orders')->where('production_order_no', 'ZZP20_900001')->update(['released_to' => 'plan', 'released_at' => now()]);
        $this->assertSame(1, $publisher->run(500, true, ['ZZP20_900001'])['candidates']);

        DB::table('generated_production_orders')->where('production_order_no', 'ZZP20_900001')->update(['production_data_at' => now()]);
        $this->assertSame(0, $publisher->run(500, true, ['ZZP20_900001'])['candidates'], 'never both routes');
    }

    public function test_screen_shows_the_plan_panel()
    {
        $this->weighing(900001, 90);
        $this->plan('ZZPL-J', 'ZZJ', '3535', 0, 500);
        app(PlanMatcher::class)->autoMatch(['ZZP20_900001']);

        $this->actingAs($this->user)->get(route('stuffing_harmonize', ['q' => 'ZZB1', 'batch' => 'ZZB1', 'item' => 'ZZJ']))
            ->assertOk()->assertSee('Planned orders')->assertSee('ZZPL-J')->assertSee('Match automatically')->assertSee('1 / 2');
    }
}
