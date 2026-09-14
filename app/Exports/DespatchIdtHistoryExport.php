<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DespatchIdtHistoryExport implements FromCollection, WithHeadings
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
                'IDT No', 'Product Code', 'Product ', 'Qty Unit Measure', 'Location', 'Transfer From', 'Customer Code', 'Order No', 'Total Issued pieces', 'Total Issued Weight', 'Total Received Pieces', 'Total Received Weight', 'Has Variance?', 'Batch No', 'Received By', 'Issued By', 'Date'
            ];
    }
}
