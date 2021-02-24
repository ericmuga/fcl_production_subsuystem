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
            $table->string('slapmark', 20);
            $table->string('item_code', 20);
            $table->double('actual_weight', 8, 2);
            $table->double('net_weight', 8, 2);
            $table->double('settlement_weight', 8, 2);
            $table->double('meat_percent', 8, 2);
            $table->string('classification_code', 20)->nullable();
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
        Schema::dropIfExists('missing_slap_data');
    }
}
