<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpicesStockTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spices_stock', function (Blueprint $table) {
            $table->id();
            $table->string('item_code');
            $table->decimal('quantity');
            $table->integer('entry_type'); // 1. Transfers 2. Consumption 3> Physical Stock
            $table->foreignId('user_id')->constrained('users');
            $table->string('physical_stock_ref')->nullable();
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
        Schema::dropIfExists('spices_stock');
    }
}
