<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->string('batch_no')->unique();
            $table->string('template_no');
            $table->string('status');
            $table->decimal('output_quantity');
            $table->decimal('from_batch')->nullable();
            $table->decimal('to_batch')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->integer('closed_by')->nullable();
            $table->integer('posted_by')->nullable();
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
        Schema::dropIfExists('batches');
    }
}
