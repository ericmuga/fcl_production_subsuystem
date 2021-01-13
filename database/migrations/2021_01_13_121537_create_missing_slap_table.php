<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMissingSlapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('missing_slap_data', function (Blueprint $table) {
            $table->id();
            $table->string('slapmark');
            $table->string('item_code');
            $table->double('net_weight', 8, 2);
            $table->double('meat_percent', 8, 2);
            $table->string('classification_code');
            $table->bigInteger('user_id');
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
        Schema::dropIfExists('missing_slap_data');
    }
}
