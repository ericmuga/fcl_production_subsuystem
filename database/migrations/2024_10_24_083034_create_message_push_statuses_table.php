<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagePushStatusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('message_push_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('queue');          // The queue name
            $table->text('message');          // The message payload
            $table->enum('status', ['pending', 'failed', 'sent'])->default('pending');
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('message_push_statuses');
    }
}
