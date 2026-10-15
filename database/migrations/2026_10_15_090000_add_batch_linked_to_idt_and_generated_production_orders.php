<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBatchLinkedToIdtAndGeneratedProductionOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('idt_transfers', function (Blueprint $table) {
            $table->boolean('batch_linked')->default(false);
        });

        Schema::table('generated_production_orders', function (Blueprint $table) {
            $table->boolean('batch_linked')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('generated_production_orders', function (Blueprint $table) {
            $table->dropColumn('batch_linked');
        });

        Schema::table('idt_transfers', function (Blueprint $table) {
            $table->dropColumn('batch_linked');
        });
    }
}
