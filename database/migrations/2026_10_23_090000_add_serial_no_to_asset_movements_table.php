<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSerialNoToAssetMovementsTable extends Migration
{
    public function up()
    {
        Schema::table('asset_movements', function (Blueprint $table) {
            $table->string('serial_no')->nullable();
        });
    }

    public function down()
    {
        Schema::table('asset_movements', function (Blueprint $table) {
            $table->dropColumn('serial_no');
        });
    }
}
