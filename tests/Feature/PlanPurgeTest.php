<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BcPlans\BcStaging;
use App\Services\BcPlans\PlanPurge;
use App\Services\BcPlans\PlanSource;
use App\Services\BcPlans\PlanSync;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Purge of BC orders nobody has acted on: WMS checks its side, locks the order and asks BC to delete it. */
class PlanPurgeTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $staging;
    public $requested = [];
    public $answers = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::firstOrCreate(['username' => 'zz_test_purge'], ['email' => 'zz_test_purge@example.invalid', 'role' => 'user']);
        $test = $this;
        $this->staging = $this->createMock(BcStaging::class);
        $this->staging->method('requestDelete')->willReturnCallback(function ($no) use ($test) {
            $test->requested[] = $no;
            return 'requested';
        });
        $this->staging->method('closeRequestStatuses')->willReturnCallback(fn($nos, $type = null) => array_intersect_key($test->answers, array_flip($nos)));
        $this->app->instance(PlanPurge::class, new PlanPurge($this->staging));

        $this->plan('ZZPG-FREE', 'released');
        $this->plan('ZZPG-OPEN', 'open');
        $this->plan('ZZPG-WEIGHED', 'open');
        $this->plan('ZZPG-STARTED', 'open');
        $this->plan('ZZPG-EXEC', 'open');
        $this->plan('ZZPG-CLOSED', 'closed');
        $now = now();
        DB::table('plan_batch_weighings')->insert([
            'batch_code' => 'ZZPG-WEIGHED-1', 'plan_no' => 'ZZPG-WEIGHED', 'item_no' => 'ZJ1', 'line_type' => 'output',
            'gross_kg' => 10, 'net_kg' => 10, 'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('bc_plan_batches')->where('plan_no', 'ZZPG-STARTED')->update(['wms_status' => 'in_progress']);
        DB::table('generated_production_orders')->insert([
            'production_order_no' => 'ZZPG-WO-1', 'plan_order_no' => 'ZZPG-EXEC', 'line_no' => 1000, 'line_type' => 'output', 'item_no' => 'ZJ1',
            'quantity' => 5, 'uom' => 'KG', 'step' => 1, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function plan(string $no, string $status): void
    {
        $now = now();
        DB::table('bc_plan_orders')->insert([
            'plan_no' => $no, 'item_no' => 'ZJ1', 'description' => 'Purge test', 'location_code' => '2055', 'plan_date' => today()->toDateString(),
            'uom' => 'KG', 'batch_uom' => 'BATCH', 'batch_size_base' => 80, 'planned_qty' => 80, 'executed_qty' => 0, 'remaining_qty' => 80,
            'status' => $status, 'section' => 'Sausage', 'sections' => 'Sausage', 'process' => 'Packing', 'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('bc_plan_order_components')->insert([
            'plan_no' => $no, 'plan_line_no' => 10000, 'line_no' => 10000, 'item_no' => 'ZG1', 'description' => 'Mix', 'uom' => 'KG',
            'qty_per' => 1.0, 'expected_qty' => 80.0, 'remaining_qty' => 80.0, 'location_code' => '2055', 'is_by_product' => 0, 'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('bc_plan_batches')->insert([
            'batch_code' => $no . '-1', 'plan_no' => $no, 'plan_line_no' => 10000, 'batch_no' => 1, 'item_no' => 'ZJ1', 'location_code' => '2055',
            'due_date' => today()->toDateString(), 'batch_uom' => 'BATCH', 'quantity' => 1, 'base_uom' => 'KG', 'quantity_base' => 80,
            'bc_status' => 'sent', 'wms_status' => 'pending', 'source' => 'bc', 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    private function status(string $no): ?string
    {
        return DB::table('bc_plan_orders')->where('plan_no', $no)->value('status');
    }

    public function test_only_orders_nobody_acted_on_are_free()
    {
        $plans = app(PlanPurge::class)->candidates(['from' => today()->toDateString()])->keyBy('plan_no');
        $this->assertFalse($plans['ZZPG-FREE']->actioned);
        $this->assertFalse($plans['ZZPG-OPEN']->actioned);
        $this->assertStringContainsString('weighing', implode(',', $plans['ZZPG-WEIGHED']->activity));
        $this->assertStringContainsString('batch(es) started', implode(',', $plans['ZZPG-STARTED']->activity));
        $this->assertStringContainsString('generated production orders', implode(',', $plans['ZZPG-EXEC']->activity));
        $this->assertArrayNotHasKey('ZZPG-CLOSED', $plans->all());
    }

    public function test_request_locks_free_orders_and_asks_bc_refuses_the_rest()
    {
        $r = app(PlanPurge::class)->request(['ZZPG-FREE', 'ZZPG-OPEN', 'ZZPG-WEIGHED', 'ZZPG-CLOSED'], $this->user->id, 'zz');

        $this->assertSame(['ZZPG-FREE', 'ZZPG-OPEN'], $this->requested);
        $this->assertTrue($r['ZZPG-FREE']['ok']);
        $this->assertFalse($r['ZZPG-WEIGHED']['ok']);
        $this->assertFalse($r['ZZPG-CLOSED']['ok']);
        $this->assertSame('purging', $this->status('ZZPG-OPEN'));
        $this->assertSame('open', DB::table('bc_plan_orders')->where('plan_no', 'ZZPG-OPEN')->value('purge_from_status'));
        $this->assertSame('open', $this->status('ZZPG-WEIGHED'));

        // no more weighing on an order being purged
        $this->actingAs($this->user)->postJson('/planned-orders/ZZPG-OPEN/batches/ZZPG-OPEN-1/weighings', ['item_no' => 'ZJ1', 'gross_kg' => 5])
            ->assertStatus(422);
    }

    public function test_bc_answer_deletes_or_restores()
    {
        app(PlanPurge::class)->request(['ZZPG-FREE', 'ZZPG-OPEN'], $this->user->id, 'zz');
        $this->answers = [
            'ZZPG-FREE' => ['status' => 'completed', 'message' => ''],
            'ZZPG-OPEN' => ['status' => 'held', 'message' => 'WMS_PURGE_KEPT: ZZPG-OPEN was not deleted: item ledger entries are posted'],
        ];
        $r = app(PlanPurge::class)->refresh();

        $this->assertSame(['deleted' => 1, 'kept' => 1], $r);
        $this->assertSame('withdrawn', $this->status('ZZPG-FREE'));
        $this->assertSame('cancelled', DB::table('bc_plan_batches')->where('plan_no', 'ZZPG-FREE')->value('wms_status'));
        $this->assertSame('open', $this->status('ZZPG-OPEN'));
        $this->assertStringContainsString('item ledger entries', DB::table('bc_plan_orders')->where('plan_no', 'ZZPG-OPEN')->value('purge_message'));
    }

    public function test_forget_removes_withdrawn_orders_never_acted_on()
    {
        DB::table('bc_plan_orders')->whereIn('plan_no', ['ZZPG-FREE', 'ZZPG-WEIGHED'])->update(['status' => 'withdrawn']);
        $n = app(PlanPurge::class)->forget(['ZZPG-FREE', 'ZZPG-WEIGHED']);

        $this->assertSame(1, $n);
        $this->assertNull($this->status('ZZPG-FREE'));
        $this->assertSame(0, DB::table('bc_plan_order_components')->where('plan_no', 'ZZPG-FREE')->count());
        $this->assertSame(0, DB::table('bc_plan_batches')->where('plan_no', 'ZZPG-FREE')->count());
        $this->assertSame('withdrawn', $this->status('ZZPG-WEIGHED'), 'kept for the record');
    }

    public function test_sync_does_not_reopen_an_order_being_purged()
    {
        app(PlanPurge::class)->request(['ZZPG-FREE'], $this->user->id, 'zz');
        $source = $this->createMock(PlanSource::class);
        $source->method('name')->willReturn('test');
        (new PlanSync($source))->ingest([[
            'plan_no' => 'ZZPG-FREE', 'item_no' => 'ZJ1', 'description' => 'Changed', 'location_code' => '2055', 'plan_date' => today()->toDateString(),
            'uom' => 'KG', 'batch_uom' => null, 'batch_size_base' => null, 'section' => 'Sausage', 'sections' => 'Sausage', 'released_from' => null,
            'planned_qty' => 90, 'executed_qty' => 0, 'remaining_qty' => 90, 'status' => 'released', 'bc_modified_at' => null,
            'closed_at' => null, 'closed_by' => null, 'components' => [], 'batches' => [],
        ]]);
        $this->assertSame('purging', $this->status('ZZPG-FREE'));
    }

    public function test_page_lists_and_requests()
    {
        $this->actingAs($this->user)->get('/planned-orders/purge')->assertOk()
            ->assertSee('ZZPG-FREE')->assertSee('ZZPG-WEIGHED')->assertSee('Acted on, kept');
        $this->actingAs($this->user)->post('/planned-orders/purge', ['plans' => ['ZZPG-FREE']])->assertRedirect();
        $this->assertSame(['ZZPG-FREE'], $this->requested);
        $this->assertSame('purging', $this->status('ZZPG-FREE'));
    }

    public function test_command_lists_without_apply_and_requests_with_it()
    {
        $this->artisan('bc:purge-plans', ['--from' => today()->toDateString()])->assertExitCode(0);
        $this->assertSame([], $this->requested);
        $this->artisan('bc:purge-plans', ['--from' => today()->toDateString(), '--plan' => ['ZZPG-FREE', 'ZZPG-WEIGHED'], '--apply' => true])->assertExitCode(0);
        $this->assertSame(['ZZPG-FREE'], $this->requested);
    }
}
