<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReceivedColumnsToLairageTransfers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('lairage_transfers', function (Blueprint $table) {
            $table->integer('received_qty')->nullable();
            $table->integer('received_by')->nullable();
            $table->timestamp('received_date_time')->nullable();
            $table->boolean('receiver_rejected')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('lairage_transfers', function (Blueprint $table) {
            $table->dropColumn('received_qty');
            $table->dropColumn('received_by');
            $table->dropColumn('received_date_time');
            $table->dropColumn('receiver_rejected');
        });
    }
}
