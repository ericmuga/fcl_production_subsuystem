<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNarrationToAssetMovementsTable extends Migration
{
    public function up()
    {
        Schema::table('asset_movements', function (Blueprint $table) {
            $table->longText('narration')->nullable();
        });
    }

    public function down()
    {
        Schema::table('asset_movements', function (Blueprint $table) {
            $table->dropColumn('narration');
        });
    }
}
