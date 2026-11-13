<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Planned orders: WMS can move an order to another location. location_code stays the location in use (everything
 * reads it); bc_location_code / bc_section (and the components' bc_location_code) keep what BC says, so the sync can update them without undoing the edit
 * and the edit can be reset.
 */
class AddLocationOverrideToBcPlanOrders extends Migration
{
    public function up()
    {
        Schema::table('bc_plan_orders', function (Blueprint $table) {
            $table->string('bc_location_code', 10)->nullable();
            $table->string('bc_section', 30)->nullable();
            $table->boolean('location_overridden')->default(false);
            $table->unsignedBigInteger('location_changed_by')->nullable();
            $table->dateTime('location_changed_at')->nullable();
        });
        // Components at the order's BC location follow the order when it moves; the rest stay where BC put them.
        Schema::table('bc_plan_order_components', function (Blueprint $table) {
            $table->string('bc_location_code', 10)->nullable();
        });
        DB::table('bc_plan_order_components')->update(['bc_location_code' => DB::raw('location_code')]);
        DB::table('bc_plan_orders')->update([
            'bc_location_code' => DB::raw('location_code'),
            'bc_section' => DB::raw('section'),
        ]);
    }

    public function down()
    {
        Schema::table('bc_plan_order_components', function (Blueprint $table) {
            $table->dropColumn('bc_location_code');
        });
        Schema::table('bc_plan_orders', function (Blueprint $table) {
            $table->dropColumn(['bc_location_code', 'bc_section', 'location_overridden', 'location_changed_by', 'location_changed_at']);
        });
    }
}
