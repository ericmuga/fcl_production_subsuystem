<?php

use App\Services\RecipeDraftReplicator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class CreateRecipeDataDraftTable extends Migration
{
    /**
     * A working copy of RecipeData that can be edited freely while the production
     * order generation is being proved out. Columns and nullability mirror the live
     * table exactly, so anything that is valid here is valid there - see
     * config/recipes.php for the switch that decides which of the two is read.
     */
    public function up()
    {
        Schema::create('recipe_data_draft', function (Blueprint $table) {
            $table->increments('id');
            $table->string('process', 255);
            $table->string('output_item', 255);
            $table->string('recipe', 255)->nullable();
            $table->string('output_item_dec', 255)->nullable();
            $table->string('output_item_uom', 50);
            $table->float('batch_size');
            $table->string('output_item_location', 255);
            $table->string('input_item', 255);
            $table->string('input_item_desc', 255)->nullable();
            $table->string('input_item_uom', 50);
            $table->float('input_item_qt_per');
            $table->string('input_item_location', 255);
            $table->string('process_code', 20)->nullable();
            $table->string('no_series', 20)->nullable();
            $table->string('routing', 20)->nullable();
            $table->timestamps();

            // The recipe graph is walked by input_item and read back by recipe, so
            // both are indexed - the live table is large enough that the draft will
            // be too once it is seeded from it.
            $table->index('input_item');
            $table->index('output_item');
            $table->index('recipe');
        });

        $this->seedFromLive();
    }

    /**
     * An empty draft is of no use to anyone, so it is filled from live as soon as it
     * exists - on deployment this means the screen is ready to use without a second
     * command.
     *
     * Guarded on both sides: --if-empty semantics mean a re-run can never discard
     * edits, and a failure is logged rather than thrown, because the schema change
     * has succeeded and rolling it back over a data copy would be worse.
     */
    private function seedFromLive(): void
    {
        try {
            $result = (new RecipeDraftReplicator)->replicate(true);

            $message = $result['skipped']
                ? $result['reason']
                : 'Seeded recipe_data_draft with ' . number_format($result['copied']) . ' line(s) from RecipeData.';

            Log::info($message);
        } catch (\Exception $e) {
            $message = 'Could not seed recipe_data_draft: ' . $e->getMessage()
                . ' Run `php artisan recipes:replicate-draft` once the source is reachable.';

            Log::warning($message);
        }

        // Migrations have no console writer of their own, and this is worth seeing
        // during a deployment rather than only in the log afterwards.
        if (PHP_SAPI === 'cli') {
            fwrite(STDOUT, '  ' . $message . PHP_EOL);
        }
    }

    public function down()
    {
        Schema::dropIfExists('recipe_data_draft');
    }
}
