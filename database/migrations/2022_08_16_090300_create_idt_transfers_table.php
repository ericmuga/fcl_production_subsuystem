<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdtTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('idt_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('product_code');
            $table->string('location_code');
            $table->string('chiller_code');
            $table->integer('total_crates');
            $table->integer('full_crates');
            $table->integer('incomplete_crate_pieces');
            $table->integer('total_pieces');
            $table->decimal('total_weight');
            $table->foreignId('user_id')->constrained('users');
            $table->string('received_by');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('idt_transfers');
    }
}
