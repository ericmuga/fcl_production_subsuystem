<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGeneratedProductionOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('generated_production_orders', function (Blueprint $table) {
            $table->id();

            // Mirrors the shape of a ProductionData line, but kept in its own table so
            // generated rows never mix with the BC-sourced ones.
            $table->string('production_order_no');
            $table->integer('line_no');
            $table->string('item_no');
            $table->decimal('quantity', 18, 5);
            $table->string('uom')->nullable();
            $table->string('location_code')->nullable();
            $table->string('bin_code')->nullable();
            $table->string('routing')->nullable();
            $table->string('external_document_no')->nullable();

            // Where this line sits in the chain from the weighed item to Packing
            $table->string('process')->nullable();
            $table->string('recipe')->nullable();
            $table->unsignedTinyInteger('step');
            $table->string('line_type');

            // What it was generated from
            $table->unsignedBigInteger('idt_transfer_id')->nullable();
            $table->string('weighed_item')->nullable();
            $table->string('packed_item')->nullable();
            $table->string('batch_no')->nullable();
            $table->decimal('net_weight', 18, 5)->nullable();

            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->date('transaction_date')->nullable();
            $table->tinyInteger('published')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();

            $table->index('production_order_no');
            $table->index('idt_transfer_id');
            $table->index(['transaction_date', 'published']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('generated_production_orders');
    }
}
