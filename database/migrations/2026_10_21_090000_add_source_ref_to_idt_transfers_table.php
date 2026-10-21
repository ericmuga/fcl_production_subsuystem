<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSourceRefToIdtTransfersTable extends Migration
{
    /**
     * external_ref now holds the sales portal return reference no (RET-...), shared by every line of a return,
     * so the duplicate check moves to source_ref (RF-{id}, one per portal transfer).
     *
     * @return void
     */
    public function up()
    {
        Schema::table('idt_transfers', function (Blueprint $table) {
            $table->string('source_ref', 64)->nullable();
        });

        DB::statement("UPDATE idt_transfers SET source_ref = external_ref WHERE external_ref LIKE 'RF-%'");

        // filtered index so the many existing NULL rows don't clash (SQL Server treats NULLs as equal in unique indexes)
        DB::statement('CREATE UNIQUE INDEX idt_transfers_source_ref_unique ON idt_transfers (source_ref) WHERE source_ref IS NOT NULL');

        DB::statement('DROP INDEX idt_transfers_external_ref_unique ON idt_transfers');
        DB::statement('CREATE INDEX idt_transfers_external_ref_index ON idt_transfers (external_ref)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP INDEX idt_transfers_external_ref_index ON idt_transfers');
        DB::statement("UPDATE idt_transfers SET external_ref = source_ref WHERE source_ref IS NOT NULL");
        DB::statement('CREATE UNIQUE INDEX idt_transfers_external_ref_unique ON idt_transfers (external_ref) WHERE external_ref IS NOT NULL');

        DB::statement('DROP INDEX idt_transfers_source_ref_unique ON idt_transfers');

        Schema::table('idt_transfers', function (Blueprint $table) {
            $table->dropColumn('source_ref');
        });
    }
}
