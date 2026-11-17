<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Every execution order carries the BC plan order it fulfils (integration map S8, CP2) and a WMS transaction id that
 * BC uses as idempotency key (CP19, CP23). Added to the tables that become execution orders today.
 */
class AddPlanOrderNoToExecutionTables extends Migration
{
    private $tables = ['generated_production_orders', 'idt_transfers', 'beheading_data', 'butchery_data', 'deboned_data', 'beef_slicing'];

    public function up()
    {
        foreach ($this->tables as $name) {
            if (!Schema::hasTable($name)) {
                continue;
            }
            Schema::table($name, function (Blueprint $table) use ($name) {
                if (!Schema::hasColumn($name, 'plan_order_no')) {
                    $table->string('plan_order_no', 20)->nullable();
                    $table->index('plan_order_no', $name . '_plan_order_no_index');
                }
                if (!Schema::hasColumn($name, 'transaction_id')) {
                    $table->string('transaction_id', 64)->nullable();
                    $table->index('transaction_id', $name . '_transaction_id_index');
                }
            });
        }
    }

    public function down()
    {
        foreach ($this->tables as $name) {
            if (!Schema::hasTable($name)) {
                continue;
            }
            Schema::table($name, function (Blueprint $table) use ($name) {
                if (Schema::hasColumn($name, 'plan_order_no')) {
                    $table->dropIndex($name . '_plan_order_no_index');
                    $table->dropColumn('plan_order_no');
                }
                if (Schema::hasColumn($name, 'transaction_id')) {
                    $table->dropIndex($name . '_transaction_id_index');
                    $table->dropColumn('transaction_id');
                }
            });
        }
    }
}
