<?php

namespace App\Exports;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DebonedLinesExport implements FromCollection, WithHeadings
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
                'Item Code', 'Product Name', 'Product Type', 'Process Code', 'Total Crates', 'Total Pieces', 'Batch No', 'Splitted',  'Total Net Weight', 'Recorded By'
            ];
    }
}
