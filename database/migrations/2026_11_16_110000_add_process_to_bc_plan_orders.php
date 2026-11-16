<?php

use App\Services\BcPlans\PlanProcess;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/** Process step of a plan within its section (Packing, Stuffing, Chopping, ...), for order selection. */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('bc_plan_orders', 'process')) {
            Schema::table('bc_plan_orders', function (Blueprint $table) {
                $table->string('process', 50)->nullable()->after('sections');
                $table->index(['section', 'process'], 'bc_plan_orders_section_process_index');
            });
        }

        // Existing plans: the sync only rewrites a plan when BC changes it.
        foreach (DB::table('bc_plan_orders')->whereNull('process')->get(['id', 'item_no', 'location_code']) as $p) {
            $process = PlanProcess::for((string) $p->item_no, $p->location_code);
            if ($process !== null) {
                DB::table('bc_plan_orders')->where('id', $p->id)->update(['process' => $process]);
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('bc_plan_orders', 'process')) {
            Schema::table('bc_plan_orders', function (Blueprint $table) {
                $table->dropIndex('bc_plan_orders_section_process_index');
                $table->dropColumn('process');
            });
        }
    }
};
