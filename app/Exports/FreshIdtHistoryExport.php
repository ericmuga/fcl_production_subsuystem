<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FreshIdtHistoryExport implements FromCollection, WithHeadings
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
