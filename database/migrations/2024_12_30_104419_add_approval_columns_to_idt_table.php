<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApprovalColumnsToIdtTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('idt_transfers', function (Blueprint $table) {
            $table->boolean('requires_approval')->default(false)->nullable();
            $table->boolean('approved')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('idt_transfers', function (Blueprint $table) {
            $table->dropColumn(['requires_approval', 'approved', 'approved_by']);
        });
    }
}
