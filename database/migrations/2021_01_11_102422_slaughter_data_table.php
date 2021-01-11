<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class SlaughterDataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('slaughter_data', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no');
            $table->string('slapmark');
            $table->string('item_code');
            $table->double('net_weight', 8, 2);
            $table->double('meat_percent', 8, 2);
            $table->string('classification_code');
            $table->bigInteger('user_id');
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
        Schema::dropIfExists('slaughter_data');
    }
}
