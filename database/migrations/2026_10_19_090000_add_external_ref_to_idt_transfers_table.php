<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddExternalRefToIdtTransfersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('idt_transfers', function (Blueprint $table) {
            // reference from an external system e.g. sales portal returns (RF-{id}), used to avoid duplicate postings
            $table->string('external_ref', 64)->nullable();
        });

        // filtered index so the many existing NULL rows don't clash (SQL Server treats NULLs as equal in unique indexes)
        DB::statement('CREATE UNIQUE INDEX idt_transfers_external_ref_unique ON idt_transfers (external_ref) WHERE external_ref IS NOT NULL');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('DROP INDEX idt_transfers_external_ref_unique ON idt_transfers');

        Schema::table('idt_transfers', function (Blueprint $table) {
            $table->dropColumn('external_ref');
        });
    }
}
