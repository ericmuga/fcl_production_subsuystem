<?php

namespace App\Exports;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DespatchIdtHistoryExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $export = Session::get('session_export_data');
        return $export;
    }

    public function headings(): array
    {
        return
            [
                'Product Code', 'Product ', 'Qty Unit Measure', 'Location', 'Transfer From', 'Customer Code', 'Order No','Total Issued pieces', 'Total Issued Weight', 'Total Received Pieces', 'Total Received Weight', 'Has Variance?', 'Batch No', 'Received By', 'Date'
            ];
    }
}
