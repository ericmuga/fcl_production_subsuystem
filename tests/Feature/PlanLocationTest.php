<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BcPlans\PlanSource;
use App\Services\BcPlans\PlanSync;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Moving a planned order to another location in WMS: what follows, what the BC sync keeps, and the reset. */
class PlanLocationTest extends TestCase
{
    use DatabaseTransactions;

    private $user;

    private function bcPlan(array $over = []): array
    {
        return array_merge([
            'plan_no' => 'TLOC-1', 'item_no' => 'J9000001', 'description' => 'Test sausage', 'location_code' => '3535',
            'plan_date' => today()->toDateString(), 'uom' => 'KG', 'planned_qty' => 160.0, 'executed_qty' => 0.0, 'remaining_qty' => 160.0,
            'status' => 'open', 'section' => 'Despatch', 'sections' => 'Despatch', 'bc_modified_at' => now()->toDateTimeString(),
            'closed_at' => null, 'closed_by' => null,
            'components' => [
                ['plan_line_no' => 10000, 'line_no' => 10000, 'item_no' => 'G1000', 'description' => 'Mix', 'uom' => 'KG', 'qty_per' => 1,
                 'expected_qty' => 160, 'remaining_qty' => 160, 'location_code' => '3535', 'flushing_method' => 'Manual', 'is_by_product' => false],
                ['plan_line_no' => 10000, 'line_no' => 20000, 'item_no' => 'G2000', 'description' => 'Casing', 'uom' => 'KG', 'qty_per' => 0.01,
                 'expected_qty' => 1.6, 'remaining_qty' => 1.6, 'location_code' => '2010', 'flushing_method' => 'Manual', 'is_by_product' => false],
            ],
        ], $over);
    }

    private function sync(array $plan): void
    {
        $src = new class([$plan]) implements PlanSource {
            public $plans;
            public function __construct($plans) { $this->plans = $plans; }
            public function fetch(string $from, string $to): array { return $this->plans; }
            public function name(): string { return 'test'; }
            public function close(string $planNo, ?string $user = null): void {}
        };
        (new PlanSync($src))->sync(today()->toDateString(), today()->toDateString());
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::firstOrCreate(['username' => 'zz_test_planner'], ['email' => 'zz_test_planner@example.invalid', 'role' => 'user']);
        Cache::put('bc_plan.locations', ['2010' => 'Sausage store', '2055' => 'Sausage line', '3535' => 'Despatch'], 600);
        $this->sync($this->bcPlan());
        DB::table('bc_plan_batches')->insert(['batch_code' => 'TLOC-1-W001', 'plan_no' => 'TLOC-1', 'plan_line_no' => 10000, 'batch_no' => 1,
            'item_no' => 'J9000001', 'location_code' => '3535', 'quantity' => 1, 'quantity_base' => 80, 'base_uom' => 'KG', 'bc_status' => 'sent',
            'wms_status' => 'pending', 'source' => 'wms', 'created_at' => now(), 'updated_at' => now()]);
    }

    private function plan()
    {
        return DB::table('bc_plan_orders')->where('plan_no', 'TLOC-1')->first();
    }

    private function componentLocations(): array
    {
        return DB::table('bc_plan_order_components')->where('plan_no', 'TLOC-1')->orderBy('line_no')->pluck('location_code')->all();
    }

    public function test_moving_an_order_moves_output_batches_and_components_at_its_location()
    {
        $this->actingAs($this->user)->postJson('/planned-orders/TLOC-1/location', ['location_code' => '2055'])
            ->assertOk()->assertJsonPath('plan.location_code', '2055');

        $p = $this->plan();
        $this->assertSame('2055', $p->location_code);
        $this->assertSame('3535', $p->bc_location_code);
        $this->assertSame('Sausage', $p->section);                  // section follows the location prefix (20)
        $this->assertSame(['2055', '2010'], $this->componentLocations()); // the one BC put elsewhere stays
        $this->assertSame('2055', DB::table('bc_plan_batches')->where('batch_code', 'TLOC-1-W001')->value('location_code'));

        // the fulfil form posts the output at the new location
        $this->actingAs($this->user)->get('/planned-orders/TLOC-1/fulfil')->assertOk()
            ->assertSee('name="lines[0][location_code]" value="2055"', false);
    }

    public function test_bc_sync_keeps_the_moved_location_and_reset_restores_bc()
    {
        $this->actingAs($this->user)->postJson('/planned-orders/TLOC-1/location', ['location_code' => '2055'])->assertOk();

        // BC changes the order (qty) and its location: WMS keeps 2055, records BC's new location
        $bc = $this->bcPlan(['planned_qty' => 240.0, 'remaining_qty' => 240.0, 'location_code' => '3536']);
        $bc['components'][0]['location_code'] = '3536';   // BC moves the order's own components with it
        $this->sync($bc);
        $p = $this->plan();
        $this->assertSame('2055', $p->location_code);
        $this->assertSame('3536', $p->bc_location_code);
        $this->assertEquals(240, (float) $p->planned_qty);
        $this->assertSame(['2055', '2010'], $this->componentLocations());

        $this->actingAs($this->user)->postJson('/planned-orders/TLOC-1/location', ['reset' => true])->assertOk();
        $p = $this->plan();
        $this->assertSame('3536', $p->location_code);
        $this->assertSame(0, (int) $p->location_overridden);
        $this->assertSame(['3536', '2010'], $this->componentLocations());
    }

    public function test_unknown_location_and_closed_orders_are_refused()
    {
        $u = $this->actingAs($this->user);
        $u->postJson('/planned-orders/TLOC-1/location', ['location_code' => '9999'])->assertStatus(422);
        DB::table('bc_plan_orders')->where('plan_no', 'TLOC-1')->update(['status' => 'closed']);
        $u->postJson('/planned-orders/TLOC-1/location', ['location_code' => '2055'])->assertStatus(422);
        $this->assertSame('3535', $this->plan()->location_code);
        $u->getJson('/planned-orders/TLOC-1/details')->assertJsonPath('location.editable', false);
    }
}
