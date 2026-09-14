<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SlaughterLinesExport implements FromCollection, WithHeadings
{
    private Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return
            [
                'Receipt No', 'Slapmark', 'Item Code', 'Item', 'Vendor No', 'Vendor Name', 'Scale Reading', 'Net Weight', 'Meat %', 'Settlement Weight', 'Classification', 'Weight Capture', 'User', 'Created at', 'Updated at'
            ];
    }
}
