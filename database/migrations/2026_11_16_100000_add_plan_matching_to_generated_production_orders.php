<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Draft production orders are matched to BC plans after the fact (not only at generation), automatically
 * or by hand, and are released once: to the plan route (BC staging, with the plan no.) or to ProductionData.
 */
class AddPlanMatchingToGeneratedProductionOrders extends Migration
{
    public function up()
    {
        Schema::table('generated_production_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('generated_production_orders', 'plan_matched_by')) {
                $table->string('plan_matched_by', 20)->nullable();   // generation | auto | manual | none (manual: no plan)
                $table->dateTime('plan_matched_at')->nullable();
                $table->dateTime('released_at')->nullable();         // review mode: released by harmonize
                $table->string('released_to', 20)->nullable();       // plan | production_data | local
            }
        });
    }

    public function down()
    {
        Schema::table('generated_production_orders', function (Blueprint $table) {
            $table->dropColumn(['plan_matched_by', 'plan_matched_at', 'released_at', 'released_to']);
        });
    }
}
