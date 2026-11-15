<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BcPlans\BcStaging;
use App\Services\BcPlans\EndOfDay;
use App\Services\BcPlans\ExecutionPublisher;
use App\Services\BcPlans\PlanSource;
use App\Services\BcPlans\PlanSync;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Weighing station (item search, vessel tare, post batch -> BC now), vessels per section and end of day. */
class PlanWeighingTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $admin;
    private $vessel;
    public $published = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::firstOrCreate(['username' => 'zz_test_weigher'], ['email' => 'zz_test_weigher@example.invalid', 'role' => 'user']);
        $this->admin = User::firstOrCreate(['username' => 'zz_test_wadmin'], ['email' => 'zz_test_wadmin@example.invalid']);
        $this->admin->forceFill(['role' => 'admin'])->save();   // role is not mass assignable
        $now = now();
        DB::table('bc_plan_orders')->insert([
            'plan_no' => 'TWP-1', 'item_no' => 'J9000001', 'description' => 'Test sausage', 'location_code' => '2055', 'plan_date' => today()->toDateString(),
            'uom' => 'KG', 'batch_uom' => 'BATCH', 'batch_size_base' => 80, 'planned_qty' => 160, 'executed_qty' => 0, 'remaining_qty' => 160,
            'status' => 'open', 'section' => 'Sausage', 'sections' => 'Sausage', 'released_from' => 'TFP-1', 'created_at' => $now, 'updated_at' => $now,
        ]);
        DB::table('bc_plan_order_components')->insert([
            'plan_no' => 'TWP-1', 'plan_line_no' => 10000, 'line_no' => 10000, 'item_no' => 'G1000', 'description' => 'Pork mix', 'uom' => 'KG',
            'qty_per' => 1.0, 'expected_qty' => 160.0, 'remaining_qty' => 160.0, 'location_code' => '2010', 'is_by_product' => 0, 'created_at' => $now, 'updated_at' => $now,
        ]);
        foreach ([1, 2] as $n) {
            DB::table('bc_plan_batches')->insert([
                'batch_code' => "TWP-1-00{$n}", 'plan_no' => 'TWP-1', 'plan_line_no' => 10000, 'batch_no' => $n, 'item_no' => 'J9000001',
                'location_code' => '2055', 'due_date' => today()->toDateString(), 'batch_uom' => 'BATCH', 'quantity' => 1, 'base_uom' => 'KG',
                'quantity_base' => 80, 'bc_status' => 'sent', 'wms_status' => 'pending', 'source' => 'bc', 'firm_planned_no' => 'TFP-1',
                'created_at' => $now, 'updated_at' => $now,
            ]);
        }
        $this->vessel = DB::table('section_vessels')->insertGetId(['section' => 'Sausage', 'name' => 'Test crate', 'tare_kg' => 1.8,
            'is_default' => 1, 'active' => 1, 'created_at' => $now, 'updated_at' => $now]);

        // BC is not called from tests: record what would be sent.
        $test = $this;
        $this->app->instance(ExecutionPublisher::class, new class($test) extends ExecutionPublisher {
            private $t;
            public function __construct($t) { $this->t = $t; }
            public function publishNow(array $orderNos): array
            {
                $this->t->published = array_merge($this->t->published, $orderNos);
                return ['candidates' => count($orderNos), 'staged' => count($orderNos), 'duplicate' => 0, 'rejected' => 0, 'errors' => 0, 'statuses' => 0];
            }
        });
    }

    private function weigh(array $over = [])
    {
        return $this->actingAs($this->user)->postJson('/planned-orders/TWP-1/batches/TWP-1-001/weighings', array_merge([
            'item_no' => 'J9000001', 'description' => 'Test sausage', 'line_type' => 'output', 'gross_kg' => 45.6,
            'vessel_id' => $this->vessel, 'vessel_count' => 2, 'manual' => true,
        ], $over));
    }

    public function test_station_lists_orders_and_shows_the_batch_with_order_plan_and_date()
    {
        $this->actingAs($this->user)->get('/planned-orders/weigh?section=Sausage')->assertOk()->assertSee('TWP-1')->assertSee('Select an order');
        $this->actingAs($this->user)->get('/planned-orders/weigh?plan=TWP-1')->assertOk()
            ->assertSee('TWP-1-001')->assertSee('TFP-1')->assertSee('Weigh item')->assertSee('Test crate');
    }

    public function test_station_script_does_not_use_jquery_before_the_layout_loads_it()
    {
        // jQuery/Bootstrap load at the end of the layout: the page script must look jQuery up when used, not on load.
        $html = $this->actingAs($this->user)->get('/planned-orders/weigh?plan=TWP-1')->assertOk()->getContent();
        $this->assertStringNotContainsString('var $ = window.jQuery,', $html);
        $this->assertStringContainsString('planned-orders\/scale-reading', $html);
    }

    public function test_scale_is_read_through_the_server()
    {
        DB::table('scale_configs')->insert(['scale' => 'ZZ Scale 1', 'comport' => 'COM9', 'section' => 'sausage', 'ip_address' => '10.9.9.9', 'tareweight' => 0, 'user_id' => $this->user->id]);
        \Illuminate\Support\Facades\Http::fake([
            'http://10.9.9.9*' => \Illuminate\Support\Facades\Http::response(json_encode(json_encode(['success' => true, 'response' => '45.62'])), 200),
        ]);
        $this->actingAs($this->user)->getJson('/planned-orders/scale-reading?plan=TWP-1&scale=ZZ%20Scale%201')
            ->assertOk()->assertJsonPath('success', true)->assertJsonPath('weight', 45.62);
        \Illuminate\Support\Facades\Http::assertSent(fn($r) => str_ends_with($r->url(), '/COM9') && str_starts_with($r->url(), 'http://10.9.9.9'));

        $this->actingAs($this->user)->getJson('/planned-orders/scale-reading?plan=TWP-1&scale=Nope')->assertStatus(422);
    }

    public function test_item_search_lists_the_order_items_first()
    {
        $r = $this->actingAs($this->user)->getJson('/planned-orders/TWP-1/items?q=')->assertOk();
        $this->assertSame(['J9000001', 'G1000'], array_column($r->json('items'), 'item_no'));
        $this->assertSame('output', $r->json('items.0.type'));
        $this->actingAs($this->user)->getJson('/planned-orders/TWP-1/items?q=pork')->assertJsonPath('items.0.item_no', 'G1000');
    }

    public function test_vessel_tare_comes_off_the_gross_weight()
    {
        $this->weigh()->assertOk()->assertJsonPath('ok', true);
        $w = DB::table('plan_batch_weighings')->where('batch_code', 'TWP-1-001')->first();
        $this->assertEquals(3.6, (float) $w->tare_kg);       // 2 x 1.8 kg crate
        $this->assertEquals(42.0, (float) $w->net_kg);       // 45.6 - 3.6
        $this->assertSame('Test crate', $w->vessel_name);
        $this->assertSame('in_progress', DB::table('bc_plan_batches')->where('batch_code', 'TWP-1-001')->value('wms_status'));

        $this->weigh(['gross_kg' => 3])->assertStatus(422);                              // less than the vessels
        DB::table('bc_plan_orders')->where('plan_no', 'TWP-1')->update(['status' => 'closing']);
        $this->weigh()->assertStatus(422);                                               // closed for the day
    }

    public function test_posting_a_batch_sums_weighings_into_one_order_and_sends_it_to_bc_now()
    {
        $this->weigh(['gross_kg' => 45.6]);                                              // 42.0 net
        $this->weigh(['gross_kg' => 41.6]);                                              // 38.0 net
        $this->weigh(['item_no' => 'G1000', 'description' => 'Pork mix', 'line_type' => 'consumption', 'gross_kg' => 85.6, 'vessel_count' => 2]); // 82.0

        $this->actingAs($this->user)->post('/planned-orders/TWP-1/batches/TWP-1-001/post', ['batch_completed' => 1])
            ->assertRedirect(route('planned_orders_weigh', ['plan' => 'TWP-1', 'batch' => 'TWP-1-002']));

        $batchId = DB::table('bc_plan_batches')->where('batch_code', 'TWP-1-001')->value('id');
        $rows = DB::table('generated_production_orders')->where('production_order_no', 'WB' . $batchId)->orderBy('line_no')->get();
        $this->assertCount(2, $rows);
        $this->assertSame('output', $rows[0]->line_type);
        $this->assertEquals(80.0, (float) $rows[0]->quantity);
        $this->assertEquals(82.0, (float) $rows[1]->quantity);
        $this->assertSame('TWP-1', $rows[0]->plan_order_no);
        $this->assertSame(['WB' . $batchId], $this->published);                         // sent to BC straight away
        $this->assertSame('completed', DB::table('bc_plan_batches')->where('id', $batchId)->value('wms_status'));
        $this->assertSame(0, DB::table('plan_batch_weighings')->where('batch_code', 'TWP-1-001')->whereNull('wms_order_no')->count());
    }

    public function test_only_vessel_admins_change_vessels()
    {
        $this->actingAs($this->user)->post('/planned-orders/vessels', ['section' => 'Sausage', 'name' => 'Tray', 'tare_kg' => 0.5])->assertStatus(403);
        $this->actingAs($this->admin)->post('/planned-orders/vessels', ['section' => 'Sausage', 'name' => 'Tray', 'tare_kg' => 0.5, 'is_default' => 1, 'active' => 1])
            ->assertRedirect(route('planned_orders_vessels'));
        $this->assertSame('Tray', DB::table('section_vessels')->where('section', 'Sausage')->where('is_default', 1)->value('name'));
        $this->assertSame(1, DB::table('section_vessels')->where('section', 'Sausage')->where('is_default', 1)->count());
    }

    public function test_end_of_day_requests_the_close_and_the_order_closes_when_bc_has_finished_it()
    {
        $source = new class implements PlanSource {
            public $closed = [];
            public function fetch(string $from, string $to): array { return []; }
            public function name(): string { return 'test'; }
            public function close(string $planNo, ?string $user = null): void { $this->closed[] = $planNo; }
        };
        $eod = new EndOfDay();

        // weighings not posted: not closed
        $this->weigh();
        $r = $eod->close(['TWP-1'], $this->user->id, 'zz', $source);
        $this->assertFalse($r['TWP-1']['ok']);
        DB::table('plan_batch_weighings')->where('plan_no', 'TWP-1')->delete();

        $r = $eod->close(['TWP-1'], $this->user->id, 'zz', $source);
        $this->assertTrue($r['TWP-1']['ok']);
        $this->assertSame(['TWP-1'], $source->closed);
        $this->assertSame('closing', DB::table('bc_plan_orders')->where('plan_no', 'TWP-1')->value('status'));
        $this->assertSame(0, DB::table('bc_plan_batches')->where('plan_no', 'TWP-1')->where('wms_status', 'pending')->count());

        // a sync that still sees the order released does not reopen it
        $plan = ['plan_no' => 'TWP-1', 'item_no' => 'J9000001', 'description' => 'Test sausage', 'location_code' => '2055', 'plan_date' => today()->toDateString(),
            'uom' => 'KG', 'planned_qty' => 160.0, 'executed_qty' => 0.0, 'remaining_qty' => 160.0, 'status' => 'open', 'section' => 'Sausage', 'sections' => 'Sausage',
            'bc_modified_at' => now()->toDateTimeString(), 'closed_at' => null, 'closed_by' => null, 'components' => [], 'batches' => []];
        $src = new class([$plan]) implements PlanSource {
            public $p; public function __construct($p) { $this->p = $p; }
            public function fetch(string $from, string $to): array { return $this->p; }
            public function name(): string { return 'test'; }
            public function close(string $planNo, ?string $user = null): void {}
        };
        (new PlanSync($src))->sync(today()->toDateString(), today()->toDateString());
        $this->assertSame('closing', DB::table('bc_plan_orders')->where('plan_no', 'TWP-1')->value('status'));

        // BC's job has run
        $staging = new class extends BcStaging {
            public function __construct() {}
            public function closeRequestStatuses(array $planNos): array { return ['TWP-1' => ['status' => 'completed', 'message' => '']]; }
        };
        $this->assertSame(1, $eod->refresh($staging));
        $this->assertSame('closed', DB::table('bc_plan_orders')->where('plan_no', 'TWP-1')->value('status'));
    }
}
