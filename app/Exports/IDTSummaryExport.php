<?php

namespace App\Exports;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class IDTSummaryExport implements FromCollection, withHeadings
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
            ['Product Code', 'Product Description', 'Sent Weight', 'Received Weight', 'Sent Pieces', 'Received Pieces'];
    }
}
