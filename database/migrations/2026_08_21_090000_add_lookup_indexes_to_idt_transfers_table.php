<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLookupIndexesToIdtTransfersTable extends Migration
{
    /**
     * idt_transfers carries the clustered primary key and nothing else, so every
     * screen that filters it by date - the stuffing panel, the per-batch report,
     * the IDT dashboards - scans all 1.3M rows to find the last day or two.
     *
     * Two indexes cover the shapes actually queried:
     *   (product_code, created_at) - the stuffing and backfill lookups, which
     *                                narrow to a set of item codes then a date range
     *   (created_at)               - the date-only filters
     */
    public function up()
    {
        Schema::table('idt_transfers', function (Blueprint $table) {
            $table->index(['product_code', 'created_at'], 'idt_transfers_product_created_idx');
            $table->index('created_at', 'idt_transfers_created_at_idx');
        });
    }

    public function down()
    {
        Schema::table('idt_transfers', function (Blueprint $table) {
            $table->dropIndex('idt_transfers_product_created_idx');
            $table->dropIndex('idt_transfers_created_at_idx');
        });
    }
}
