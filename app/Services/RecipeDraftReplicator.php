<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

/**
 * Fills recipe_data_draft from the live RecipeData table.
 *
 * One implementation behind three callers: the Copy from Live button, the
 * recipes:replicate-draft command, and the migration that creates the table - so
 * the chunking and the column list can never drift apart between them.
 */
class RecipeDraftReplicator
{
    /**
     * Every column carried across. id is deliberately absent: the draft has its own
     * identity column, and a copy is a fresh set of rows rather than a mirror of
     * live's keys.
     */
    const COLUMNS = [
        'process', 'output_item', 'recipe', 'output_item_dec', 'output_item_uom',
        'batch_size', 'output_item_location', 'input_item', 'input_item_desc',
        'input_item_uom', 'input_item_qt_per', 'input_item_location',
        'process_code', 'no_series', 'routing',
    ];

    /**
     * 17 columns written per row against SQL Server's 2100 bind parameter ceiling
     * caps an insert at 123 rows, so batches are kept below that.
     */
    const INSERT_CHUNK = 100;

    const READ_CHUNK = 500;

    private $draft;

    private $live;

    public function __construct(?string $draft = null, ?string $live = null)
    {
        $this->draft = $draft ?: config('recipes.draft_table', 'recipe_data_draft');
        $this->live = $live ?: config('recipes.live_table', 'RecipeData');
    }

    public function draftTable(): string
    {
        return $this->draft;
    }

    public function liveTable(): string
    {
        return $this->live;
    }

    public function draftCount(): int
    {
        return DB::table($this->draft)->count();
    }

    public function liveCount(): int
    {
        return DB::table($this->live)->count();
    }

    /**
     * Replace the draft with a fresh copy of live.
     *
     * @param  bool  $onlyIfEmpty  Skip when the draft already holds rows. Use for
     *                             anything automated, so a deploy can never discard
     *                             recipes someone is part way through editing.
     * @return array{copied:int,skipped:bool,reason:?string}
     */
    public function replicate(bool $onlyIfEmpty = false): array
    {
        foreach ([$this->live, $this->draft] as $table) {
            if (!Schema::hasTable($table)) {
                throw new RuntimeException("Table [{$table}] does not exist.");
            }
        }

        if ($onlyIfEmpty && $this->draftCount() > 0) {
            return [
                'copied' => 0,
                'skipped' => true,
                'reason' => "{$this->draft} already holds " . $this->draftCount() . ' line(s); left untouched.',
            ];
        }

        $now = now();
        $copied = 0;

        // All or nothing: a failure part way through must not leave the draft
        // holding half a recipe set, which would generate wrong orders silently.
        DB::transaction(function () use ($now, &$copied) {
            DB::table($this->draft)->delete();

            DB::table($this->live)
                ->select(self::COLUMNS)
                ->orderBy('id')
                ->chunk(self::READ_CHUNK, function ($rows) use ($now, &$copied) {
                    $batch = collect($rows)
                        ->map(fn($row) => (array) $row + ['created_at' => $now, 'updated_at' => $now])
                        ->all();

                    foreach (array_chunk($batch, self::INSERT_CHUNK) as $slice) {
                        DB::table($this->draft)->insert($slice);
                        $copied += count($slice);
                    }
                });
        });

        $this->forgetCaches();

        return ['copied' => $copied, 'skipped' => false, 'reason' => null];
    }

    /**
     * The recipe graph is cached for 10 hours, so a replicated draft is not visible
     * to the next weighing until the cache is dropped.
     */
    public function forgetCaches(): void
    {
        foreach ((array) config('recipes.cache_keys', []) as $key) {
            \Illuminate\Support\Facades\Cache::forget($key);
        }
    }
}
