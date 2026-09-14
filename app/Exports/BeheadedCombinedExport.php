<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class BeheadedCombinedExport implements FromCollection, WithHeadings
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
                'Item Code', 'Carcass Type', 'No. of Carcasses', 'Total Net Weight'
            ];
    }
}
