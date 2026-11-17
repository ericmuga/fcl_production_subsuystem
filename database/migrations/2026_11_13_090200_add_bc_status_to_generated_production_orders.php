<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * BC status of each execution order pushed over the SQL route (integration map S10): when it was staged in BC and
 * what BC reported back (pending / held / completed / error / rejected).
 */
class AddBcStatusToGeneratedProductionOrders extends Migration
{
    public function up()
    {
        Schema::table('generated_production_orders', function (Blueprint $table) {
            $table->dateTime('bc_staged_at')->nullable();
            $table->string('bc_status', 20)->nullable();
            $table->string('bc_status_message', 250)->nullable();
            $table->index(['bc_staged_at', 'bc_status'], 'gpo_bc_staged_status_index');
        });
    }

    public function down()
    {
        Schema::table('generated_production_orders', function (Blueprint $table) {
            $table->dropIndex('gpo_bc_staged_status_index');
            $table->dropColumn(['bc_staged_at', 'bc_status', 'bc_status_message']);
        });
    }
}
