<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DespatchIDTSummaryReport implements FromCollection, WithHeadings
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
