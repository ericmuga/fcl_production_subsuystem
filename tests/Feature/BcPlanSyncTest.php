<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BcPlans\PlanResolver;
use App\Services\BcPlans\PlanSource;
use App\Services\BcPlans\PlanSync;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * BC daily plan -> WMS (integration map S4-S5). Runs against the migrated WMS database (fcl_wms_dev locally),
 * inside a transaction that is rolled back.
 */
class BcPlanSyncTest extends TestCase
{
    use DatabaseTransactions;

    private function plan(array $over = []): array
    {
        return array_merge([
            'plan_no' => 'TPLAN-0001', 'item_no' => 'J31015501', 'description' => 'Beef Sausage Catering 1Kg',
            'location_code' => '3535', 'plan_date' => '2026-11-16', 'uom' => 'KG',
            'planned_qty' => 9200.0, 'executed_qty' => 0.0, 'remaining_qty' => 9200.0, 'status' => 'open',
            'bc_modified_at' => '2026-11-13 08:00:00', 'closed_at' => null, 'closed_by' => null,
            'components' => [
                ['plan_line_no' => 10000, 'line_no' => 10000, 'item_no' => 'G2306', 'description' => 'Beef sausage mix', 'uom' => 'KG',
                 'qty_per' => 80.808, 'expected_qty' => 9292.92, 'remaining_qty' => 9292.92, 'location_code' => '3535',
                 'flushing_method' => 'Manual', 'is_by_product' => false],
                ['plan_line_no' => 10000, 'line_no' => 20000, 'item_no' => 'G9999', 'description' => 'Trimmings (by-product)', 'uom' => 'KG',
                 'qty_per' => -2.5, 'expected_qty' => -287.5, 'remaining_qty' => -287.5, 'location_code' => '3535',
                 'flushing_method' => 'Manual', 'is_by_product' => true],
            ],
        ], $over);
    }

    private function source(array $plans): PlanSource
    {
        return new class($plans) implements PlanSource {
            public $plans;
            public $closed = [];
            public function __construct($plans) { $this->plans = $plans; }
            public function fetch(string $from, string $to): array
            {
                return array_values(array_filter($this->plans, fn($p) => $p['plan_date'] >= $from && $p['plan_date'] <= $to));
            }
            public function name(): string { return 'test'; }
            public function close(string $planNo, ?string $user = null): void { $this->closed[] = $planNo; }
        };
    }

    public function test_sync_is_idempotent_and_tracks_changes()
    {
        $src = $this->source([$this->plan(), $this->plan(['plan_no' => 'TPLAN-0002', 'item_no' => 'J31010502', 'components' => []])]);
        $sync = new PlanSync($src);

        $first = $sync->sync('2026-11-16', '2026-11-16');
        $this->assertSame(['fetched' => 2, 'created' => 2, 'updated' => 0, 'unchanged' => 0, 'withdrawn' => 0], $first);
        $this->assertSame(2, DB::table('bc_plan_order_components')->where('plan_no', 'TPLAN-0001')->count());
        $this->assertEquals(1, DB::table('bc_plan_order_components')->where('plan_no', 'TPLAN-0001')->where('item_no', 'G9999')->value('is_by_product'));

        // Re-running with the same BC data changes nothing
        $this->assertSame(['fetched' => 2, 'created' => 0, 'updated' => 0, 'unchanged' => 2, 'withdrawn' => 0], $sync->sync('2026-11-16', '2026-11-16'));

        // BC nets an execution order and drops the second plan
        $src->plans = [$this->plan(['executed_qty' => 1200.0, 'remaining_qty' => 8000.0])];
        $third = $sync->sync('2026-11-16', '2026-11-16');
        $this->assertSame(1, $third['updated']);
        $this->assertSame(1, $third['withdrawn']);
        $this->assertEquals(8000, DB::table('bc_plan_orders')->where('plan_no', 'TPLAN-0001')->value('remaining_qty'));
        $this->assertSame('withdrawn', DB::table('bc_plan_orders')->where('plan_no', 'TPLAN-0002')->value('status'));
    }

    public function test_resolver_finds_open_plan_for_item_location_and_day_only()
    {
        (new PlanSync($this->source([$this->plan()])))->sync('2026-11-16', '2026-11-16');
        $resolver = new PlanResolver();

        $this->assertSame('TPLAN-0001', $resolver->openPlanFor('J31015501', '3535', '2026-11-16'));
        $this->assertNull($resolver->openPlanFor('J31015501', '3600', '2026-11-16'));
        $this->assertNull((new PlanResolver())->openPlanFor('J31015501', '3535', '2026-11-17')); // no carry-over (CP4)
        $this->assertTrue($resolver->isItemOnPlan('TPLAN-0001', 'G9999'));      // by-product allowed (CP6)
        $this->assertFalse($resolver->isItemOnPlan('TPLAN-0001', 'G0001'));
        $this->assertSame('GPO:P19_1E05_812', PlanResolver::transactionId('gpo', 'P19_1E05_812'));
    }

    public function test_closed_plan_is_not_resolved_and_screen_shows_progress()
    {
        (new PlanSync($this->source([$this->plan(), $this->plan(['plan_no' => 'TPLAN-0003', 'item_no' => 'J31010502', 'status' => 'closed', 'components' => []])])))
            ->sync('2026-11-16', '2026-11-16');
        $this->assertNull((new PlanResolver())->openPlanFor('J31010502', '3535', '2026-11-16'));

        DB::table('generated_production_orders')->insert([
            'production_order_no' => 'P20_TEST_1', 'line_no' => 1000, 'item_no' => 'J31015501', 'quantity' => 2300, 'uom' => 'KG',
            'location_code' => '3535', 'step' => 1, 'line_type' => 'output', 'plan_order_no' => 'TPLAN-0001',
            'transaction_id' => 'GPO:P20_TEST_1', 'transaction_date' => '2026-11-16',
        ]);

        $user = User::query()->first() ?? User::forceCreate(['username' => 'plan_test', 'email' => 'plan_test@example.invalid', 'password' => bcrypt('x')]);
        $res = $this->actingAs($user)->get('/production-plan?date=2026-11-16');
        $res->assertOk();
        $res->assertSee('TPLAN-0001');
        $res->assertSee('2,300.00');   // produced so far
        $res->assertSee('25%');        // 2300 / 9200
        $res->assertSee('Close day');
    }
}
