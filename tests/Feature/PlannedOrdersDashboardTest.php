<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Dashboard section of planned production orders (filters) and batch fulfilment: one WMS order per batch, the batch
 * target autopopulated, actual kg entered, by-products negative, batch marked completed.
 */
class PlannedOrdersDashboardTest extends TestCase
{
    use DatabaseTransactions;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::firstOrCreate(['username' => 'zz_test_planner'], ['email' => 'zz_test_planner@example.invalid', 'role' => 'user']);
        $now = now();
        DB::table('bc_plan_orders')->insert([
            ['plan_no' => 'TPO-1', 'item_no' => 'J9000001', 'description' => 'Test sausage', 'location_code' => '3535', 'plan_date' => today()->toDateString(),
             'uom' => 'KG', 'batch_uom' => 'BATCH', 'planned_qty' => 160, 'executed_qty' => 0, 'remaining_qty' => 160, 'status' => 'open',
             'section' => 'Sausage', 'sections' => 'Sausage|High Care', 'released_from' => 'FPO-77', 'created_at' => $now, 'updated_at' => $now],
            ['plan_no' => 'TPO-2', 'item_no' => 'G9000002', 'description' => 'Test cut', 'location_code' => '1570', 'plan_date' => today()->toDateString(),
             'uom' => 'KG', 'batch_uom' => null, 'planned_qty' => 50, 'executed_qty' => 0, 'remaining_qty' => 50, 'status' => 'firm_planned',
             'section' => 'Butchery', 'sections' => 'Butchery', 'released_from' => null, 'created_at' => $now, 'updated_at' => $now],
        ]);
        DB::table('bc_plan_order_components')->insert([
            ['plan_no' => 'TPO-1', 'plan_line_no' => 10000, 'line_no' => 10000, 'item_no' => 'G1000', 'description' => 'Pork trim', 'uom' => 'KG',
             'qty_per' => 70, 'expected_qty' => 140, 'remaining_qty' => 140, 'location_code' => '2010', 'is_by_product' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['plan_no' => 'TPO-1', 'plan_line_no' => 10000, 'line_no' => 20000, 'item_no' => 'G2000', 'description' => 'Fat', 'uom' => 'KG',
             'qty_per' => -5, 'expected_qty' => -10, 'remaining_qty' => -10, 'location_code' => '2010', 'is_by_product' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
        foreach ([1, 2] as $n) {
            DB::table('bc_plan_batches')->insert([
                'batch_code' => "TPO-1-00{$n}", 'plan_no' => 'TPO-1', 'plan_line_no' => 10000, 'batch_no' => $n, 'item_no' => 'J9000001',
                'location_code' => '3535', 'due_date' => today()->toDateString(), 'batch_uom' => 'BATCH', 'quantity' => 1, 'base_uom' => 'KG',
                'quantity_base' => 80, 'bc_status' => 'sent', 'wms_status' => 'pending', 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function test_dashboard_card_links_to_planned_orders_page()
    {
        $this->actingAs($this->user)->get('/')->assertOk()->assertSee('Planned Orders')->assertSee('/planned-orders')->assertDontSee('TPO-1');
    }

    public function test_planned_orders_page_lists_and_filters()
    {
        $u = $this->actingAs($this->user);
        $u->get('/planned-orders')->assertOk()->assertSee('TPO-1')->assertSee('TPO-2');
        $u->get('/planned-orders?pp_section=Butchery')->assertSee('TPO-2')->assertDontSee('TPO-1');
        $u->get('/planned-orders?pp_planned=FPO-77')->assertSee('TPO-1')->assertDontSee('TPO-2');
        $u->get('/planned-orders?pp_item=J9000001')->assertSee('TPO-1')->assertDontSee('TPO-2');
        $u->get('/planned-orders?pp_order=TPO-2')->assertSee('TPO-2')->assertDontSee('>TPO-1<', false);
        // High Care is one of TPO-1's sections, not its main one
        $u->get('/planned-orders?pp_section=High+Care')->assertSee('TPO-1');
    }

    public function test_page_is_prefiltered_from_last_monday_to_today()
    {
        DB::table('bc_plan_orders')->where('plan_no', 'TPO-2')->update(['plan_date' => today()->startOfWeek()->subDays(3)->toDateString()]);
        $u = $this->actingAs($this->user);
        $u->get('/planned-orders')->assertSee('value="' . today()->startOfWeek()->toDateString() . '"', false)->assertDontSee('TPO-2');
        $u->get('/planned-orders?pp_from=&pp_to=')->assertSee('TPO-2');
    }

    public function test_order_details_for_the_modal()
    {
        $this->actingAs($this->user)->getJson('/planned-orders/TPO-2/details')->assertOk()
            ->assertJsonPath('plan.status', 'firm_planned')->assertJsonPath('fulfil_url', null);
        $this->actingAs($this->user)->getJson('/planned-orders/TPO-1/details')->assertOk()
            ->assertJsonCount(2, 'components')->assertJsonCount(2, 'batches');
    }

    public function test_fulfil_form_shows_batch_target_and_one_batch_of_components()
    {
        $this->actingAs($this->user)->get('/planned-orders/TPO-1/fulfil')->assertOk()
            ->assertSee('TPO-1-001')->assertSee('Target output')->assertSee('80.00')
            ->assertSee('70.000')   // 140 kg trim for 160 kg planned -> 70 kg for an 80 kg batch
            ->assertSee('By-product');
    }

    public function test_recording_a_batch_creates_one_wms_order_linked_to_the_batch()
    {
        $this->actingAs($this->user)->post('/planned-orders/TPO-1/fulfil', [
            'batch_code' => 'TPO-1-001',
            'batch_completed' => 1,
            'lines' => [
                ['type' => 'output', 'item_no' => 'J9000001', 'quantity' => 78.4, 'uom' => 'KG', 'location_code' => '3535', 'by_product' => 0],
                ['type' => 'consumption', 'item_no' => 'G1000', 'quantity' => 71.2, 'uom' => 'KG', 'location_code' => '2010', 'by_product' => 0],
                ['type' => 'consumption', 'item_no' => 'G2000', 'quantity' => 4.5, 'uom' => 'KG', 'location_code' => '2010', 'by_product' => 1],
            ],
        ])->assertRedirect();

        $batch = DB::table('bc_plan_batches')->where('batch_code', 'TPO-1-001')->first();
        $this->assertSame('completed', $batch->wms_status);
        $rows = DB::table('generated_production_orders')->where('batch_code', 'TPO-1-001')->orderBy('line_no')->get();
        $this->assertCount(3, $rows);
        $this->assertSame('WB' . $batch->id, $rows[0]->production_order_no);
        $this->assertSame($batch->wms_order_no, $rows[0]->production_order_no);
        $this->assertSame('output', $rows[0]->line_type);
        $this->assertEquals(78.4, (float) $rows[0]->quantity);
        $this->assertEquals(-4.5, (float) $rows[2]->quantity);   // by-product = negative consumption
        $this->assertSame('TPO-1', $rows[0]->plan_order_no);
        $this->assertSame(1, (int) $rows[0]->plan_batch_no);
        $this->assertSame(1, (int) $rows[0]->published);          // never picked up by the legacy export
        $this->assertSame('PLB:WB' . $batch->id, $rows[0]->transaction_id);

        // the next open batch is offered now
        $this->actingAs($this->user)->get('/planned-orders/TPO-1/fulfil')->assertSee('TPO-1-002');
        // and a completed batch is refused
        $this->actingAs($this->user)->post('/planned-orders/TPO-1/fulfil', [
            'batch_code' => 'TPO-1-001',
            'lines' => [['type' => 'output', 'item_no' => 'J9000001', 'quantity' => 10]],
        ]);
        $this->assertSame(3, DB::table('generated_production_orders')->where('batch_code', 'TPO-1-001')->count());
    }

    public function test_only_open_plans_can_be_fulfilled()
    {
        $this->actingAs($this->user)->post('/planned-orders/TPO-2/fulfil', [
            'batch_code' => 'X',
            'lines' => [['type' => 'output', 'item_no' => 'G9000002', 'quantity' => 10]],
        ]);
        $this->assertSame(0, DB::table('generated_production_orders')->where('plan_order_no', 'TPO-2')->count());
    }
}
