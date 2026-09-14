<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PostedChoppingLinesExport implements FromCollection, WithHeadings
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
                'Batch No', 'Recipe No', 'Item Code', 'Item Name', 'Temp Name', 'Type', 'Main Product', 'UOM', 'Qty', 'batch Size', 'Total Qty Used', 'Date'
            ];
    }
}
