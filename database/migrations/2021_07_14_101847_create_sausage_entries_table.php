<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSausageEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sausage_entries', function (Blueprint $table) {
            $table->id();
            $table->string('origin_timestamp');
            $table->string('scanner_ip');
            $table->string('barcode');
            $table->integer('occurrences')->default(0);
            $table->unique(['origin_timestamp', 'scanner_ip', 'barcode']);
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
        Schema::dropIfExists('sausage_entries');
    }
}
