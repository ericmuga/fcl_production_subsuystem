<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** BC posts released orders to WMS: stored, split into batch orders that carry the BC order, the plan and the date. */
class BcReleasedOrdersApiTest extends TestCase
{
    use DatabaseTransactions;

    private const URL = '/api/v1/bc/released-orders';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.valid_bearer_token' => 'test-token']);
    }

    private function order(array $over = []): array
    {
        return array_merge([
            'order_no' => 'TRPO-1', 'status' => 'released', 'pushed' => true, 'firm_planned_no' => 'TFPO-9', 'item_no' => 'J9000001',
            'description' => 'Test sausage', 'location_code' => '2055', 'due_date' => '2026-11-16', 'base_uom' => 'KG',
            'planned_qty_base' => 200, 'executed_qty_base' => 0, 'quantity_base' => 200, 'finished_qty_base' => 0, 'batch_size_base' => 80,
            'routing_locations' => ['2010', '2055'],
            'components' => [
                ['plan_line_no' => 10000, 'line_no' => 10000, 'item_no' => 'G1000', 'description' => 'Mix', 'uom' => 'KG', 'qty_per' => 1.05,
                 'expected_qty' => 210, 'remaining_qty' => 210, 'location_code' => '2010', 'flushing_method' => 'Manual', 'is_by_product' => false],
            ],
            'batches' => [
                ['batch_code' => 'TRPO-1-001', 'plan_line_no' => 10000, 'batch_no' => 1, 'item_no' => 'J9000001', 'location_code' => '2055',
                 'due_date' => '2026-11-16', 'batch_uom' => 'BATCH', 'quantity' => 1, 'base_uom' => 'KG', 'quantity_base' => 80, 'executed_qty_base' => 0, 'status' => 'sent'],
                ['batch_code' => 'TRPO-1-002', 'plan_line_no' => 10000, 'batch_no' => 2, 'item_no' => 'J9000001', 'location_code' => '2055',
                 'due_date' => '2026-11-16', 'batch_uom' => 'BATCH', 'quantity' => 1, 'base_uom' => 'KG', 'quantity_base' => 80, 'executed_qty_base' => 0, 'status' => 'sent'],
                ['batch_code' => 'TRPO-1-003', 'plan_line_no' => 10000, 'batch_no' => 3, 'item_no' => 'J9000001', 'location_code' => '2055',
                 'due_date' => '2026-11-16', 'batch_uom' => 'BATCH', 'quantity' => 0.5, 'base_uom' => 'KG', 'quantity_base' => 40, 'executed_qty_base' => 0, 'status' => 'sent'],
            ],
        ], $over);
    }

    private function send(array $orders, string $token = 'test-token')
    {
        return $this->withHeaders(['Authorization' => 'Bearer ' . $token])
            ->postJson(self::URL, ['source' => 'BC', 'sent_at' => '2026-11-14T06:00:00Z', 'orders' => $orders]);
    }

    public function test_needs_the_token()
    {
        $this->postJson(self::URL, ['orders' => [$this->order()]])->assertStatus(401);
        $this->send([$this->order()], 'wrong')->assertStatus(401);
        config(['app.valid_bearer_token' => '']);
        $this->send([$this->order()], '')->assertStatus(503);   // no token configured: refused, not open
    }

    public function test_bc_batches_become_wms_batch_orders_referencing_order_plan_and_date()
    {
        $res = $this->send([$this->order()])->assertOk()
            ->assertJsonPath('created', 1)
            ->assertJsonPath('orders.0.order_no', 'TRPO-1')
            ->assertJsonPath('orders.0.firm_planned_no', 'TFPO-9')
            ->assertJsonPath('orders.0.date', '2026-11-16')
            ->assertJsonPath('orders.0.wms_status', 'open')
            ->assertJsonPath('orders.0.section', 'Sausage')
            ->assertJsonCount(3, 'orders.0.batches');
        $this->assertSame('BC', $res->json('orders.0.batches.0.cut_by'));

        $b = DB::table('bc_plan_batches')->where('plan_no', 'TRPO-1')->orderBy('batch_no')->get();
        $this->assertSame(['TRPO-1-001', 'TRPO-1-002', 'TRPO-1-003'], $b->pluck('batch_code')->all());
        $this->assertSame('TFPO-9', $b[0]->firm_planned_no);
        $this->assertSame('2026-11-16', substr($b[0]->due_date, 0, 10));
        $plan = DB::table('bc_plan_orders')->where('plan_no', 'TRPO-1')->first();
        $this->assertSame('bc-http', $plan->source);
        $this->assertNotNull($plan->received_at);
        $this->assertSame(1, DB::table('bc_plan_order_components')->where('plan_no', 'TRPO-1')->count());

        // the same order again changes nothing
        $this->send([$this->order()])->assertOk()->assertJsonPath('unchanged', 1)->assertJsonPath('created', 0);
    }

    public function test_order_without_bc_batches_is_cut_by_wms_in_80kg_batches()
    {
        $this->send([$this->order(['order_no' => 'TRPO-2', 'batches' => []])])->assertOk()
            ->assertJsonPath('batches_cut_in_wms', 3)
            ->assertJsonPath('orders.0.batches.0.batch_code', 'TRPO-2-W001')
            ->assertJsonPath('orders.0.batches.2.quantity_kg', 40)
            ->assertJsonPath('orders.0.batches.0.cut_by', 'WMS');
        $this->assertSame('TFPO-9', DB::table('bc_plan_batches')->where('batch_code', 'TRPO-2-W001')->value('firm_planned_no'));
    }

    public function test_invalid_payload_is_rejected()
    {
        $this->send([['order_no' => 'X']])->assertStatus(422)->assertJsonPath('success', false);
    }
}
