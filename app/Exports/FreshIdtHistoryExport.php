<?php

namespace App\Exports;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FreshIdtHistoryExport implements FromCollection, WithHeadings
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
                'IDT No',
                'Product Code',
                'Location Code ',
                'Chiller Code',
                'Total Crates',
                'Total Pieces',
                'Total Weight',
                'Description',
                'Batch No',
                'Transfer Type',
                'Received Crates',
                'Received Pieces',
                'Received Weight',
                'Production Date',
                'Manual Weight',
                'Item Description',
                'Product Description',
                'Unit of Measure',
                'Count per Crate',
                'User',
                'Date'
            ];
    }
}
