<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * WMS sections a plan runs through, from the locations of its routing's work centres (config bc_plan.sections):
 * section = the section of the last operation (where the output is made), sections = all of them, '|'-separated.
 */
class AddSectionsToBcPlanOrders extends Migration
{
    public function up()
    {
        Schema::table('bc_plan_orders', function (Blueprint $table) {
            $table->string('section', 30)->nullable();
            $table->string('sections', 150)->nullable();
            $table->index(['section', 'plan_date']);
        });
    }

    public function down()
    {
        Schema::table('bc_plan_orders', function (Blueprint $table) {
            $table->dropIndex(['section', 'plan_date']);
        });
        Schema::table('bc_plan_orders', function (Blueprint $table) {
            $table->dropColumn(['section', 'sections']);
        });
    }
}
