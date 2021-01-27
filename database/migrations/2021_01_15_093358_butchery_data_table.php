<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ButcheryDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('butchery_data', function (Blueprint $table) {
            $table->id();
            $table->string('carcass_type', 20);
            $table->string('item_code', 20);
            $table->double('net_weight', 8, 2);
            $table->integer('process_code');
            $table->integer('no_of_items')->nullable();
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
        Schema::dropIfExists('butchery_data');
    }
}
