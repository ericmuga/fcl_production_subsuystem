<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemplateLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('template_lines', function (Blueprint $table) {
            $table->id();
            $table->string('template_no');
            $table->string('item_code');
            $table->index(['template_no', 'item_code']);
            $table->string('description');
            $table->decimal('percentage');
            $table->decimal('units_per_100');
            $table->string('type');
            $table->string('main_product');
            $table->string('shortcode');
            $table->string('unit_measure');
            $table->string('location');
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
        Schema::dropIfExists('template_lines');
    }
}
