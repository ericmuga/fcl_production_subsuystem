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
            $table->string('enrolment_no', 20);
            $table->string('vendor_tag', 20);
            $table->string('receipt_no', 20);
            $table->string('vendor_no', 20);
            $table->string('vendor_name', 50);
            $table->string('receipt_date')->nullable();
            $table->string('item_code', 20);
            $table->string('description');
            $table->integer('received_qty');
            $table->bigInteger('user_id');
            $table->timestamp('slaughter_date');
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
