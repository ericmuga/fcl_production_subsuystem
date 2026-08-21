<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * Reads a sheet back into the draft recipe table.
 *
 * Two layouts are accepted: the one RecipeDraftExport produces (leading ID
 * column, so edits can be matched to existing rows) and the plain RecipeData
 * layout that starts at Process. Which one it is, is taken from the header.
 *
 * Unlike the live RecipeImport this does not swallow failures - rejected rows are
 * collected with their sheet line number and handed back for display, and the
 * whole import runs in a transaction so a bad file cannot leave the table
 * half-written.
 */
class RecipeDraftImport implements ToCollection
{
    const MODE_REPLACE = 'replace';
    const MODE_MERGE = 'merge';

    private $mode;

    private $table;

    public $created = 0;

    public $updated = 0;

    /**
     * Fatal - nothing is written while any of these stand.
     */
    public $errors = [];

    /**
     * Imported, but worth saying out loud. Live RecipeData carries rows with a blank
     * output_item (the deboning step definitions), so a faithful copy of live has to
     * be allowed through rather than rejected.
     */
    public $warnings = [];

    public function __construct(string $mode = self::MODE_REPLACE, ?string $table = null)
    {
        $this->mode = $mode === self::MODE_MERGE ? self::MODE_MERGE : self::MODE_REPLACE;
        $this->table = $table ?: config('recipes.draft_table', 'recipe_data_draft');
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            $this->errors[] = 'The sheet is empty.';

            return;
        }

        $header = $rows->shift();

        // The export leads with ID; a plain RecipeData sheet leads with Process.
        $hasId = strtolower(trim((string) ($header[0] ?? ''))) === 'id';
        $offset = $hasId ? 1 : 0;

        $now = now();
        $insert = [];
        $update = [];

        foreach ($rows as $index => $row) {
            // +2 rather than +1: the header was shifted off, and sheets are 1-based.
            $line = $index + 2;

            $process = $this->text($row[$offset + 0] ?? null);
            $outputItem = $this->text($row[$offset + 1] ?? null);
            $inputItem = $this->text($row[$offset + 7] ?? null);

            // A row with none of the three key columns is blank padding, not an error.
            if ($process === '' && $outputItem === '' && $inputItem === '') {
                continue;
            }

            if (strtolower($process) === 'process' || strtolower($outputItem) === 'output item') {
                continue; // a repeated header block
            }

            // Process is the one column that has to be there - it identifies the
            // step, and live has no row without it.
            if ($process === '') {
                $this->errors[] = "Row {$line}: Process is blank.";
                continue;
            }

            $blank = [];
            if ($outputItem === '') { $blank[] = 'Output Item'; }
            if ($inputItem === '') { $blank[] = 'Input Item'; }

            if ($blank) {
                $this->warnings[] = "Row {$line} ({$process}): blank " . implode(' and ', $blank) . '.';
            }

            $values = [
                'process' => $process,
                'output_item' => $outputItem,
                'recipe' => $this->nullable($row[$offset + 2] ?? null),
                'output_item_dec' => $this->nullable($row[$offset + 3] ?? null),
                'output_item_uom' => $this->text($row[$offset + 4] ?? null),
                'batch_size' => $this->number($row[$offset + 5] ?? null),
                'output_item_location' => $this->text($row[$offset + 6] ?? null),
                'input_item' => $inputItem,
                'input_item_desc' => $this->nullable($row[$offset + 8] ?? null),
                'input_item_uom' => $this->text($row[$offset + 9] ?? null),
                'input_item_qt_per' => $this->number($row[$offset + 10] ?? null),
                'input_item_location' => $this->text($row[$offset + 11] ?? null),
                'process_code' => $this->nullable($row[$offset + 12] ?? null, 20),
                'no_series' => $this->nullable($row[$offset + 13] ?? null, 20),
                'routing' => $this->nullable($row[$offset + 14] ?? null, 20),
                'updated_at' => $now,
            ];

            $id = $hasId && is_numeric($row[0] ?? null) ? (int) $row[0] : null;

            // An id only means anything when merging - a replace has already thrown
            // the old rows away, so every row is new.
            if ($id && $this->mode === self::MODE_MERGE) {
                $update[$id] = $values;
                continue;
            }

            $values['created_at'] = $now;
            $insert[] = $values;
        }

        if ($this->errors) {
            return; // nothing is written when the file has rejected rows
        }

        DB::transaction(function () use ($insert, $update) {
            if ($this->mode === self::MODE_REPLACE) {
                DB::table($this->table)->delete();
            }

            foreach (array_chunk($insert, 100) as $chunk) {
                DB::table($this->table)->insert($chunk);
                $this->created += count($chunk);
            }

            foreach ($update as $id => $values) {
                $this->updated += DB::table($this->table)->where('id', $id)->update($values);
            }
        });
    }

    private function text($value): string
    {
        return trim((string) ($value ?? ''));
    }

    private function nullable($value, ?int $limit = null): ?string
    {
        $text = $this->text($value);

        if ($text === '' || strtoupper($text) === 'NULL') {
            return null;
        }

        return $limit ? mb_substr($text, 0, $limit) : $text;
    }

    private function number($value): float
    {
        return is_numeric($value) ? (float) $value : 0.0;
    }
}
