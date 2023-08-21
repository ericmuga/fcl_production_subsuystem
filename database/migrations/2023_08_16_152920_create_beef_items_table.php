<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBeefItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('beef_items', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // stands for both unique and indexable
            $table->string('barcode')->nullable();
            $table->string('description', 50);
            $table->string('unit_of_measure', 10)->default('KG');
            $table->string('location_code', 50);
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
        Schema::dropIfExists('beef_items');
    }
}
