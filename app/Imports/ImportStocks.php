<?php

namespace App\Imports;

use App\Models\Stock;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportStocks implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */

    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            $insert = Stock::create([
                'product_code' => $row['product_code'], // Map Excel column to your field
                'weight' => $row['weight'],
                'pieces' => $row['pieces'],
                'location_code' => $row['location_code'],
                'chiller_code' => $row['chiller_code'],
                'stock_date' => Date::excelToDateTimeObject($row['stock_date'])
            ]);
        }
    }
}
