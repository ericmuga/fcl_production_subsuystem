<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Batches cut by WMS (source 'wms') for orders BC has not batched yet: BC's "Push to WMS" is the normal route
 * (source 'bc'); until the BC build is deployed, "Refresh from BC" cuts them in WMS from the item's BATCH unit.
 */
class AddLocalBatchingToBcPlans extends Migration
{
    public function up()
    {
        Schema::table('bc_plan_batches', function (Blueprint $table) {
            $table->string('source', 3)->default('bc');
        });
        Schema::table('bc_plan_orders', function (Blueprint $table) {
            $table->decimal('batch_size_base', 18, 5)->nullable();   // kg per batch (item's BATCH unit), null = no batch unit
        });
    }

    public function down()
    {
        Schema::table('bc_plan_orders', function (Blueprint $table) {
            $table->dropColumn('batch_size_base');
        });
        Schema::table('bc_plan_batches', function (Blueprint $table) {
            $table->dropColumn('source');
        });
    }
}
