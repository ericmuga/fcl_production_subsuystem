<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Purge of orders nobody has acted on (PlanPurge): WMS asks BC to delete them. The order shows 'purging' until BC has
 * deleted it (then 'withdrawn'); when BC keeps it, it returns to purge_from_status with BC's reason in purge_message.
 */
class AddPurgeToBcPlanOrders extends Migration
{
    public function up()
    {
        Schema::table('bc_plan_orders', function (Blueprint $table) {
            $table->string('purge_from_status', 12)->nullable();
            $table->timestamp('purge_requested_at')->nullable();
            $table->unsignedBigInteger('purge_requested_by')->nullable();
            $table->string('purge_message', 250)->nullable();
        });
    }

    public function down()
    {
        Schema::table('bc_plan_orders', function (Blueprint $table) {
            $table->dropColumn(['purge_from_status', 'purge_requested_at', 'purge_requested_by', 'purge_message']);
        });
    }
}
