<?php

namespace App\Imports;

use App\Models\Stock;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportStocksExcel implements ToCollection, WithHeadingRow
{
    // public function __construct()
    // {
    //     dd('hhhhhhhhhhhhhh');
    // }

    public function collection(Collection $rows)
    {
        info('fghjkl;');
        dd('cvbnm,k');
        foreach ($rows as $row) {
            $insert = Stock::create([
                'product_code' => $row['Product Code'], // Map Excel column to your field
                'weight' => $row['Weight'],
                'pieces' => $row['Pieces'],
                'location_code' => $row['Location Code'],
                'chiller_code' => $row['Chiller Code'],
                'stock_date' => Carbon::createFromFormat('d/m/Y', $row['Stock Date'])->toDateString()
            ]);
        }
    }
}
