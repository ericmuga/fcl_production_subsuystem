<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOutputItemToIdtTransfersTable extends Migration
{
    public function up()
    {
        Schema::table('idt_transfers', function (Blueprint $table) {
            $table->string('output_item')->nullable();
        });
    }

    public function down()
    {
        Schema::table('idt_transfers', function (Blueprint $table) {
            $table->dropColumn('output_item');
        });
    }
}