<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateIdtChangelogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('idt_changelogs', function (Blueprint $table) {
            $table->id();
            $table->string('table_name');
            $table->bigInteger('item_id');
            $table->foreignId('changed_by')->constrained('users');
            $table->tinyInteger('is_processed')->default(0);
            $table->integer('total_pieces');
            $table->decimal('total_weight');
            $table->integer('previous_pieces');
            $table->decimal('previous_weight');
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
        Schema::dropIfExists('idt_changelogs');
    }
}
