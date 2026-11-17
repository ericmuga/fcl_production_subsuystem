<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BcPlans\PlanReports;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** MRP reports: execution rate and material requirements per item and location. BC is switched off: WMS figures only. */
class PlanReportsTest extends TestCase
{
    use DatabaseTransactions;

    private const D1 = '2031-03-03';
    private const D2 = '2031-03-04';

    protected function setUp(): void
    {
        parent::setUp();
        config(['bc_plan.sql.connection' => 'zz_no_such_connection']);   // BC figures fail cleanly; WMS figures still show
        $now = now();
        $plan = fn($no, $date, $status, $planned, $executed) => [
            'plan_no' => $no, 'item_no' => 'J9100001', 'description' => 'Test smokies', 'location_code' => '3535', 'plan_date' => $date,
            'uom' => 'KG', 'planned_qty' => $planned, 'executed_qty' => $executed, 'remaining_qty' => $planned - $executed,
            'status' => $status, 'section' => 'Sausage', 'sections' => 'Sausage', 'created_at' => $now, 'updated_at' => $now,
        ];
        DB::table('bc_plan_orders')->insert([
            $plan('TRR-1', self::D1, 'open', 160.0, 40.0),
            $plan('TRR-2', self::D2, 'closed', 80.0, 80.0),
            $plan('TRR-3', self::D2, 'firm_planned', 100.0, 0.0),
        ]);
        $comp = fn($no, $line, $item, $qty, $byProduct = 0) => [
            'plan_no' => $no, 'plan_line_no' => 10000, 'line_no' => $line, 'item_no' => $item, 'description' => $item, 'uom' => 'KG',
            'qty_per' => 1.0, 'expected_qty' => $qty, 'remaining_qty' => $qty, 'location_code' => '3535', 'is_by_product' => $byProduct,
            'created_at' => $now, 'updated_at' => $now,
        ];
        DB::table('bc_plan_order_components')->insert([
            $comp('TRR-1', 10000, 'G9100', 160.0),
            $comp('TRR-1', 20000, 'G9199', 5.0, 1),          // by-product: not a requirement
            $comp('TRR-3', 10000, 'G9100', 100.0),
        ]);
        $gpo = fn($order, $plan, $line, $type, $item, $qty) => [
            'production_order_no' => $order, 'plan_order_no' => $plan, 'line_no' => $line, 'line_type' => $type, 'item_no' => $item,
            'quantity' => $qty, 'step' => 1, 'published' => 1, 'created_at' => $now, 'updated_at' => $now,
        ];
        DB::table('generated_production_orders')->insert([
            $gpo('WBT-1', 'TRR-1', 1000, 'output', 'J9100001', 120.0),
            $gpo('WBT-1', 'TRR-1', 2000, 'consumption', 'G9100', 50.0),
            $gpo('WBT-2', 'TRR-2', 1000, 'output', 'J9100001', 80.0),
        ]);
    }

    public function test_execution_rate_per_item_and_location()
    {
        $rows = (new PlanReports())->execution(self::D1, self::D2, null, 'J9100001');
        $this->assertCount(1, $rows);
        $r = $rows[0];
        $this->assertEquals(240.0, $r['planned']);          // released + closed
        $this->assertEquals(100.0, $r['firm_planned']);     // shown apart, not in the rate
        $this->assertEquals(200.0, $r['posted_wms']);
        $this->assertEquals(120.0, $r['posted_bc']);
        $this->assertEquals(83.3, $r['rate_wms']);
        $this->assertEquals(50.0, $r['rate_bc']);
        $this->assertSame(2, $r['orders']);
        $this->assertSame(1, $r['closed']);

        $days = (new PlanReports())->execution(self::D1, self::D2, null, 'J9100001', null, true);
        $this->assertSame([self::D1, self::D2], array_column($days, 'date'));
        $this->assertEquals(75.0, $days[0]['rate_wms']);    // 120 of 160
    }

    public function test_material_requirements_net_of_wms_consumption()
    {
        $reports = new PlanReports();
        $rows = $reports->requirements(self::D1, self::D2, null, 'G91*');
        $this->assertSame(['G9100'], array_column($rows, 'item_no'));      // by-product left out
        $r = $rows[0];
        $this->assertEquals(100.0, $r['firm_planned_need']);
        $this->assertEquals(160.0, $r['released_need']);
        $this->assertEquals(50.0, $r['consumed']);
        $this->assertEquals(210.0, $r['remaining']);
        $this->assertNull($r['stock']);                                      // BC not reachable
        $this->assertEquals(210.0, $r['shortage']);
        $this->assertNotNull($reports->bcError);

        $days = (new PlanReports())->requirements(self::D1, self::D2, null, 'G91*', null, true);
        $this->assertSame([110.0, 100.0], array_map(fn($d) => (float) $d['remaining'], $days));
    }

    public function test_report_page_and_csv_export()
    {
        $user = User::firstOrCreate(['username' => 'zz_test_reports'], ['email' => 'zz_test_reports@example.invalid', 'role' => 'user']);
        $this->actingAs($user)->get('/planned-orders/reports?view=execution&from=' . self::D1 . '&to=' . self::D2)
            ->assertOk()->assertSee('Execution rate')->assertSee('J9100001')->assertSee('83.3%');
        $this->actingAs($user)->get('/planned-orders/reports?view=requirements&from=' . self::D1 . '&to=' . self::D2 . '&shortages=1')
            ->assertOk()->assertSee('G9100')->assertSee('could not be read');
        $csv = $this->actingAs($user)->get('/planned-orders/reports?view=requirements&from=' . self::D1 . '&to=' . self::D2 . '&export=csv');
        $csv->assertOk()->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('Remaining need', $csv->getContent());
    }
}
