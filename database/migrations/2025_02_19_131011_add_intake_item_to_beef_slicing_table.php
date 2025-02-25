<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIntakeItemToBeefSlicingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('beef_slicing', function (Blueprint $table) {
            $table->string('intake_item')->after('item_code')->nullable(); // Adjust placement as needed
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('beef_slicing', function (Blueprint $table) {
            $table->dropColumn('intake_item');
        });
    }
}
