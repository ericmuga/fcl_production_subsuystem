<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BcPlans\PlanProcess;
use App\Services\BcPlans\PlanSource;
use App\Services\BcPlans\PlanSync;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/** Process steps inside a section (Sausage: Packing, Stuffing, Chopping) for order selection. */
class PlanProcessTest extends TestCase
{
    use DatabaseTransactions;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::firstOrCreate(['username' => 'zz_test_steps'], ['email' => 'zz_test_steps@example.invalid', 'role' => 'user']);
        Schema::create('zz_recipe_steps', function (Blueprint $t) {
            $t->id();
            $t->string('output_item', 20);
            $t->string('process', 50)->nullable();
            $t->string('input_item', 20)->nullable();
        });
        DB::table('zz_recipe_steps')->insert([
            ['output_item' => 'ZJ1', 'process' => 'Packing', 'input_item' => 'ZG1'],
            ['output_item' => 'ZJ1', 'process' => 'Packing', 'input_item' => 'PKG'],
            ['output_item' => 'ZG1', 'process' => 'Stuffing', 'input_item' => 'ZG2'],
            ['output_item' => 'ZG2', 'process' => 'chopping', 'input_item' => 'MEAT'],
        ]);
        config(['production_orders.recipe_table' => 'zz_recipe_steps']);
        PlanProcess::flush();
    }

    protected function tearDown(): void
    {
        PlanProcess::flush();
        parent::tearDown();
    }

    private function plan(string $no, string $item, string $loc, ?string $process): void
    {
        DB::table('bc_plan_orders')->insert([
            'plan_no' => $no, 'item_no' => $item, 'description' => 'Test ' . $item, 'location_code' => $loc, 'plan_date' => today()->toDateString(),
            'uom' => 'KG', 'batch_uom' => 'BATCH', 'batch_size_base' => 80, 'planned_qty' => 160, 'executed_qty' => 0, 'remaining_qty' => 160,
            'status' => 'open', 'section' => 'Sausage', 'sections' => 'Sausage', 'process' => $process, 'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    public function test_step_comes_from_the_recipe_process_then_the_location()
    {
        $this->assertSame('Packing', PlanProcess::for('ZJ1', '3535'));
        $this->assertSame('Stuffing', PlanProcess::for('ZG1', '2055'));
        $this->assertSame('Chopping', PlanProcess::for('ZG2', null), 'normalised');
        $this->assertSame('Mincing', PlanProcess::for('ZNORECIPE', '2010'), 'location fallback');
        $this->assertNull(PlanProcess::for('ZNORECIPE', '9999'));
    }

    public function test_sausage_steps_are_offered_packing_stuffing_chopping_in_that_order()
    {
        $labels = array_column(PlanProcess::options(), 'label');
        $i = fn($l) => array_search($l, $labels, true);
        $this->assertNotFalse($i('Sausage'));
        $this->assertTrue($i('Sausage') < $i('Sausage - Packing'));
        $this->assertTrue($i('Sausage - Packing') < $i('Sausage - Stuffing'));
        $this->assertTrue($i('Sausage - Stuffing') < $i('Sausage - Chopping'));
    }

    public function test_station_filters_orders_by_step()
    {
        $this->plan('ZZST-PACK', 'ZJ1', '3535', 'Packing');
        $this->plan('ZZST-STUFF', 'ZG1', '2055', 'Stuffing');
        $this->plan('ZZST-CHOP', 'ZG2', '2055', 'Chopping');

        $this->actingAs($this->user)->get('/planned-orders/weigh?section=' . urlencode('Sausage:Stuffing'))->assertOk()
            ->assertSee('ZZST-STUFF')->assertDontSee('ZZST-PACK')->assertDontSee('ZZST-CHOP')->assertSee('Sausage - Stuffing');
        $this->actingAs($this->user)->get('/planned-orders/weigh?section=Sausage')->assertOk()
            ->assertSee('ZZST-PACK')->assertSee('ZZST-STUFF')->assertSee('ZZST-CHOP');
        $this->actingAs($this->user)->get('/planned-orders?pp_section=' . urlencode('Sausage:Chopping') . '&pp_from=&pp_to=')->assertOk()
            ->assertSee('ZZST-CHOP')->assertDontSee('ZZST-PACK');
    }

    public function test_sync_stores_the_step()
    {
        $plan = [
            'plan_no' => 'ZZST-SYNC', 'item_no' => 'ZG1', 'description' => 'Stuffed', 'location_code' => '2055', 'plan_date' => today()->toDateString(),
            'uom' => 'KG', 'batch_uom' => null, 'batch_size_base' => null, 'section' => 'Sausage', 'sections' => 'Sausage', 'released_from' => null,
            'planned_qty' => 100, 'executed_qty' => 0, 'remaining_qty' => 100, 'status' => 'released', 'bc_modified_at' => null,
            'closed_at' => null, 'closed_by' => null, 'components' => [], 'batches' => [],
        ];
        $source = $this->createMock(PlanSource::class);
        $source->method('name')->willReturn('test');
        (new PlanSync($source))->ingest([$plan]);

        $this->assertSame('Stuffing', DB::table('bc_plan_orders')->where('plan_no', 'ZZST-SYNC')->value('process'));
    }
}
