<?php

namespace Tests\Feature;

use App\Services\BcPlans\LocalBatcher;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** WMS-cut batches for orders BC has not batched: 80 kg each, stable codes, give way to BC's batches. */
class LocalBatcherTest extends TestCase
{
    use DatabaseTransactions;

    private function plan(string $no, string $status, float $qty, ?float $batch = 80): void
    {
        DB::table('bc_plan_orders')->insert([
            'plan_no' => $no, 'item_no' => 'J9000001', 'description' => 'Test', 'location_code' => '3535', 'plan_date' => today()->toDateString(),
            'uom' => 'KG', 'planned_qty' => $qty, 'executed_qty' => 0, 'remaining_qty' => $qty, 'status' => $status,
            'batch_size_base' => $batch, 'batch_uom' => $batch ? 'BATCH' : null, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    private function batches(string $no)
    {
        return DB::table('bc_plan_batches')->where('plan_no', $no)->orderBy('batch_no')->get();
    }

    public function test_cuts_80kg_batches_with_a_partial_last_one()
    {
        $this->plan('TLB-1', 'firm_planned', 200);
        $s = (new LocalBatcher())->run(['TLB-1']);
        $b = $this->batches('TLB-1');
        $this->assertSame(3, $s['created']);
        $this->assertSame(['TLB-1-W001', 'TLB-1-W002', 'TLB-1-W003'], $b->pluck('batch_code')->all());
        $this->assertEquals([80, 80, 40], $b->pluck('quantity_base')->map(fn($v) => (float) $v)->all());
        $this->assertEquals(0.5, (float) $b[2]->quantity);
        $this->assertSame('wms', $b[0]->source);
        // re-running changes nothing
        $this->assertSame(['orders' => 1, 'created' => 0, 'resized' => 0, 'cancelled' => 0], (new LocalBatcher())->run(['TLB-1']));
    }

    public function test_plan_change_keeps_started_batches_and_recuts_the_rest()
    {
        $this->plan('TLB-2', 'open', 240);
        (new LocalBatcher())->run(['TLB-2']);
        DB::table('bc_plan_batches')->where('batch_code', 'TLB-2-W001')->update(['wms_status' => 'completed', 'wms_order_no' => 'WB1']);
        DB::table('bc_plan_orders')->where('plan_no', 'TLB-2')->update(['planned_qty' => 130]);
        $s = (new LocalBatcher())->run(['TLB-2']);
        $b = $this->batches('TLB-2');
        $this->assertSame('completed', $b[0]->wms_status);
        $this->assertEquals(50, (float) $b[1]->quantity_base);     // 130 - 80 done = 50 left in one batch
        $this->assertSame('cancelled', $b[2]->wms_status);
        $this->assertSame(1, $s['resized']);
        $this->assertSame(1, $s['cancelled']);
    }

    public function test_bc_batches_win_and_inactive_orders_lose_pending_batches()
    {
        $this->plan('TLB-3', 'open', 80);
        $this->plan('TLB-4', 'firm_planned', 80, null);
        (new LocalBatcher())->run(['TLB-3', 'TLB-4']);
        $this->assertEquals(80, (float) $this->batches('TLB-4')[0]->quantity_base);   // no BATCH unit: the whole order is one batch

        // BC batched TLB-3: no more WMS batching for it
        DB::table('bc_plan_batches')->insert(['batch_code' => 'TLB-3-001', 'plan_no' => 'TLB-3', 'plan_line_no' => 10000, 'batch_no' => 9,
            'item_no' => 'J9000001', 'quantity' => 1, 'quantity_base' => 80, 'bc_status' => 'sent', 'wms_status' => 'pending', 'source' => 'bc',
            'created_at' => now(), 'updated_at' => now()]);
        DB::table('bc_plan_orders')->where('plan_no', 'TLB-3')->update(['planned_qty' => 400]);
        $this->assertSame(0, (new LocalBatcher())->run(['TLB-3'])['created']);

        // firm planned order released under a new no.
        DB::table('bc_plan_orders')->where('plan_no', 'TLB-4')->update(['status' => 'converted']);
        (new LocalBatcher())->run();
        $this->assertSame('cancelled', $this->batches('TLB-4')[0]->wms_status);
    }
}
