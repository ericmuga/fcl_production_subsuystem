<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stuffing orders can be held back from ProductionData until the batch has been
 * harmonized: stuffing weighs the input, the dispatch transfer weighs the output,
 * and the lines in between are rebuilt from both before BC sees them.
 */
class AddHarmonizationToGeneratedProductionOrders extends Migration
{
    public function up()
    {
        Schema::table('generated_production_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('generated_production_orders', 'harmonize_mode')) {
                $table->string('harmonize_mode', 20)->nullable();       // recipe | output_at_last | output_at_first
                $table->dateTime('harmonized_at')->nullable();
                $table->decimal('recipe_quantity', 18, 5)->nullable();  // quantity as first generated, kept for comparison
                $table->dateTime('production_data_at')->nullable();     // when written to ProductionData
                $table->index(['batch_no', 'packed_item'], 'gpo_batch_packed_index');
            }
        });
    }

    public function down()
    {
        Schema::table('generated_production_orders', function (Blueprint $table) {
            $table->dropIndex('gpo_batch_packed_index');
            $table->dropColumn(['harmonize_mode', 'harmonized_at', 'recipe_quantity', 'production_data_at']);
        });
    }
}
