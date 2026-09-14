<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StuffingWeightsHistoryExport implements FromCollection, WithHeadings
{
    private Collection $rows;

    public function __construct(Collection $rows)
    {
        $this->rows = $rows;
    }

    public function collection()
    {
        return $this->rows;
    }

    public function headings(): array
    {
        return [
            'IDT No', 'Product Code', 'Description', 'Weight', 'Batch No', 'Created At'
        ];
    }
}
