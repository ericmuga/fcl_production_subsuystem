<?php

namespace App\Exports;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PostedChoppingLinesExport implements FromCollection, WithHeadings
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
                'Batch No', 'Item Code', 'Item Name', 'Temp Name', 'Type', 'Main Product', 'UOM', 'Qty', 'batch Size', 'Total Qty Used', 'Date'
            ];
    }
}
