<?php
namespace App\Imports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Illuminate\Support\Collection;

class RecipeImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        try {
            info('Importing Recipe Data...');

            // Remove header row
            $header = $rows->shift();
            Log::info('Skipped header row:', $header->toArray());

            // Truncate table before inserting new data
            DB::table('RecipeData')->truncate();

            // Collect all valid rows
            $allData = [];

            foreach ($rows as $index => $row) {
                if (!isset($row[0], $row[1], $row[2])) {
                    Log::warning("Row $index skipped: missing required fields", $row->toArray());
                    continue;
                }

                if ($row[0] === 'Process' || $row[1] === 'Output Item') {
                    Log::warning("Row $index looks like a header and was skipped", $row->toArray());
                    continue;
                }

                $allData[] = [
                    'process' => $row[0],
                    'output_item' => $row[1],
                    'recipe' => $row[2],
                    'output_item_dec' => $row[3],
                    'output_item_uom' => $row[4],
                    'batch_size' => is_numeric($row[5]) ? (float)$row[5] : 0.0,
                    'output_item_location' => $row[6],
                    'input_item' => $row[7],
                    'input_item_desc' => $row[8],
                    'input_item_uom' => $row[9],
                    'input_item_qt_per' => is_numeric($row[10]) ? (float)$row[10] : 0.0,
                    'input_item_location' => $row[11],
                    'process_code' => $row[12],
                    'no_series' => $row[13],
                    'routing' => $row[14],
                ];
            }

            // Insert safely in chunks
            foreach (array_chunk($allData, 100) as $chunk) {
                DB::table('RecipeData')->insert($chunk);
            }
            info('Recipe Data inserted successfully in chunks.');
        } catch (\Exception $e) {
            Log::error('Error importing Recipe Data: ' . $e->getMessage(), [
                'exception' => $e,
                'rows' => $rows->toArray(),
            ]);
        }
    }
}
