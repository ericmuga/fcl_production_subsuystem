<?php

namespace App\Exports;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DespatchIDTSummaryReport implements FromCollection, WithHeadings
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
                'Product Code',
                'Product',  
                'Qty Per Unit',
                'Transfer To location',
                'Transfer From',
                'Sent Total pieces',
                'Sent Total Weight',
                'Total Received Pieces',
                'Total Received Weight',
            ];
    }
}
