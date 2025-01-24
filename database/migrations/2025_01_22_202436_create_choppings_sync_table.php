<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateChoppingsSyncTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('choppings_sync', function (Blueprint $table) {
            $table->id();
            $table->string('chopping_id')->index();
            $table->string('item_code');
            $table->decimal('weight', 10, 2);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->datetime('sync_date')->nullable(); // Added column
            $table->tinyInteger('output')->default(0); // Added column
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('choppings_sync');
    }
}
