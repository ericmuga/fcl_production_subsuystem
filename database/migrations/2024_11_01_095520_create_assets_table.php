<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('No_', 20);
            $table->string('Description', 100);
            $table->string('Fa_Code', 20);
            $table->string('Chassis', 100)->nullable();
            $table->string('Engine_No', 100)->nullable();
            $table->string('FA_Class_Code', 20);
            $table->string('Make_Brand', 150)->nullable();
            $table->string('Comments', 250)->nullable();
            $table->string('Responsible_employee', 20);
            $table->string('Location_code', 250);
            $table->string('LocationName', 250);
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
        Schema::dropIfExists('assets');
    }
}
