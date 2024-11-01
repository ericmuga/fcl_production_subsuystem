<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOffalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('offals', function (Blueprint $table) {
            $table->id();
            $table->string('product_code', 20)->nullable();
            $table->double('scale_reading', 8, 2);
            $table->double('net_weight', 8, 2);
            $table->boolean('is_manual')->default(0);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offals');
    }
}
