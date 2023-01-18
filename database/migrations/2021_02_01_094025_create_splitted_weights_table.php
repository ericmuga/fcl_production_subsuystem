<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSplittedWeightsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('splitted_weights', function (Blueprint $table) {
            $table->id();
            $table->string('parent_item', 20);
            $table->string('new_item', 20);
            $table->double('net_weight', 8, 2);
            $table->integer('process_code');
            $table->integer('percentage');
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
        Schema::dropIfExists('splitted_weights');
    }
}
