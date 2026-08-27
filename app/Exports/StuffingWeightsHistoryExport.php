<?php
namespace App\Exports;

use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StuffingWeightsHistoryExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        $export = Session::get('session_export_data');
        return $export;
    }

    public function headings(): array
    {
        return [
            'Product Code', 'Description', 'Entries', 'Total Weight'
        ];
    }
}
