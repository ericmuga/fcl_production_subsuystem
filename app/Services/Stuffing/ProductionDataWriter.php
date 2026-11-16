<?php

namespace App\Services\Stuffing;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Copies generated stuffing order lines into the BC ProductionData table, which the
 * ProcessProduction job picks up. Lines already there for the same order and line
 * number are skipped, mirroring the NOT EXISTS guard of the SQL script this replaced.
 */
class ProductionDataWriter
{
    public function enabled(): bool
    {
        return config('production_orders.target') === 'production_data';
    }

    /**
     * Whether new orders wait for harmonization before they are written. In review
     * mode they stay local until the batch is applied on the harmonize screen (or by
     * stuffing:harmonize), so BC never sees the recipe-only quantities.
     */
    public function holdsForHarmonization(): bool
    {
        return config('production_orders.harmonize') === 'review';
    }

    /**
     * @param  array  $rows  generated_production_orders rows (arrays or objects)
     * @return string        a suffix for the log message: what happened
     */
    public function write(array $rows, string $username): string
    {
        if (!$this->enabled() || !$rows) {
            return '';
        }

        $table = config('production_orders.production_data_table', 'ProductionData');
        $rows = array_map(fn($r) => (array) $r, $rows);

        try {
            $orderNos = collect($rows)->pluck('production_order_no')->unique()->values()->toArray();

            $existing = collect($orderNos)->chunk(500)->flatMap(fn($nos) => DB::table($table)
                ->whereIn('ProductionOrderNo', $nos->all())
                ->select('ProductionOrderNo', 'LineNo')
                ->get())
                ->mapWithKeys(fn($r) => [$r->ProductionOrderNo . '|' . $r->LineNo => true]);

            $decimals = (int) config('production_orders.production_data_decimals', 2);
            $pending = [];

            foreach ($rows as $row) {
                if (isset($existing[$row['production_order_no'] . '|' . $row['line_no']])) {
                    continue;
                }

                // ID is an identity column and every remaining column is NOT NULL
                $pending[] = [
                    'ProductionOrderNo' => $row['production_order_no'],
                    'LineNo' => $row['line_no'],
                    'ItemNo' => $row['item_no'],
                    'Quantity' => round((float) $row['quantity'], $decimals),
                    'UOM' => ($row['uom'] ?? null) ?: 'KG',
                    'LocationCode' => ($row['location_code'] ?? null) ?: '',
                    'BinCode' => '',
                    'UserName' => $username,
                    'Routing' => $row['routing'] ?? '',
                    'DateTime' => $row['created_at'],
                    'Status' => 0,
                    'FinishedProductionOrderNo' => '',
                    'ExternalDocumentNo' => $row['external_document_no'] ?? '',
                    'TransactionDate' => $row['transaction_date'],
                    'Published' => 0,
                ];
            }

            foreach (array_chunk($pending, 100) as $chunk) {
                DB::table($table)->insert($chunk);
            }

            $this->stamp($orderNos);

            return $pending ? ', ' . count($pending) . ' written to ' . $table : ', already present in ' . $table;
        } catch (\Exception $e) {
            // The weight and the local copy are already saved; surface the failure
            // rather than losing them.
            Log::error('Failed writing generated orders to ProductionData: ' . $e->getMessage());

            return ', but writing to ProductionData failed (' . $e->getMessage() . ')';
        }
    }

    private function stamp(array $orderNos): void
    {
        foreach (array_chunk($orderNos, 500) as $nos) {
            DB::table('generated_production_orders')
                ->whereIn('production_order_no', $nos)
                ->whereNull('production_data_at')
                ->update(['production_data_at' => now()]);
        }
    }
}
