<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeefProductProcessesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beef_product_processes', function (Blueprint $table) {
            $table->id();
            $table->string('product_code');
            $table->string('process_code');
            $table->smallInteger('product_type');

            // Create a composite unique constraint
            $table->unique(['product_code', 'process_code', 'product_type']);

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
        Schema::dropIfExists('beef_product_processes');
    }
}
