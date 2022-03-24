<?php

namespace App\Exports;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SlaughterLinesExport implements FromCollection, WithHeadings
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
                'Receipt No', 'Slapmark', 'Item Code', 'Item', 'Vendor No', 'Vendor Name', 'Scale Reading', 'Net Weight', 'Meat %', 'Settlement Weight', 'Classification', 'Weight Capture', 'User', 'Created at', 'Updated at'
            ];
    }
}
