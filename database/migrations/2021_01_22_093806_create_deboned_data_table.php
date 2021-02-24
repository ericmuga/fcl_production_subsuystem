<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDebonedDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deboned_data', function (Blueprint $table) {
            $table->id();
            $table->string('item_code', 20);
            $table->double('actual_weight', 8, 2);
            $table->double('net_weight', 8, 2);
            $table->integer('process_code')->nullable();
            $table->tinyInteger('product_type');
            $table->integer('no_of_pieces');
            $table->tinyInteger('splitted')->default(0);
            $table->foreignId('user_id')->constrained('users');
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
        Schema::dropIfExists('deboned_data');
    }
}
