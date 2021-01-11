<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ReceiptsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('enrolment_no');
            $table->string('vendor_tag');
            $table->string('receipt_no');
            $table->string('vendor_no');
            $table->string('vendor_name');
            $table->string('receipt_date')->nullable();
            $table->string('item_code');
            $table->string('description');
            $table->string('received_qty')->nullable();
            $table->bigInteger('user_id')->nullable();
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
        Schema::dropIfExists('receipts');
    }
}
