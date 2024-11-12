<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeheadingDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beheading_data', function (Blueprint $table) {
            $table->id();
            $table->string('item_code', 20);
            $table->decimal('no_of_carcass', 8, 2);
            $table->double('actual_weight', 8, 2);
            $table->double('net_weight', 8, 2);
            $table->integer('process_code');
            $table->tinyInteger('return_entry')->default(0);
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
        Schema::dropIfExists('beheading_data');
    }
}
