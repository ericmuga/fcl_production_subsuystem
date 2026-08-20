<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

/**
 * Downloads the draft recipe table in the exact column order RecipeDraftImport
 * reads back, so a download can be edited in Excel and uploaded again unchanged.
 *
 * Codes are written as text on purpose. Left to Excel's own type detection a
 * recipe like 1230E05 is read as scientific notation and comes back as
 * 123000000 - the download would quietly destroy the code it was meant to carry.
 */
class RecipeDraftExport extends DefaultValueBinder implements FromCollection, WithHeadings, WithCustomValueBinder
{
    /**
     * The only columns that are genuinely numeric: ID, Batch Size, Input Item Qty
     * Per. Everything else is a code or a description and stays text.
     */
    const NUMERIC_COLUMNS = ['A', 'G', 'L'];

    public function bindValue(Cell $cell, $value)
    {
        if ($value !== null && $value !== '' && !in_array($cell->getColumn(), self::NUMERIC_COLUMNS, true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    /**
     * Columns, in order, that both this export and the import agree on. The id is
     * carried so an edited download can be matched back to existing rows.
     */
    const COLUMNS = [
        'id',
        'process',
        'output_item',
        'recipe',
        'output_item_dec',
        'output_item_uom',
        'batch_size',
        'output_item_location',
        'input_item',
        'input_item_desc',
        'input_item_uom',
        'input_item_qt_per',
        'input_item_location',
        'process_code',
        'no_series',
        'routing',
    ];

    /**
     * @var array Filters carried over from the screen, so what you see is what you
     *            download.
     */
    private $filters;

    private $table;

    public function __construct(array $filters = [], ?string $table = null)
    {
        $this->filters = $filters;
        $this->table = $table ?: config('recipes.draft_table', 'recipe_data_draft');
    }

    public function collection()
    {
        return DB::table($this->table)
            ->select(self::COLUMNS)
            ->when($this->filters['recipe'] ?? null, fn($q, $v) => $q->where('recipe', 'like', "%{$v}%"))
            ->when($this->filters['process'] ?? null, fn($q, $v) => $q->where('process', $v))
            ->when($this->filters['output_item'] ?? null, fn($q, $v) => $q->where('output_item', 'like', "%{$v}%"))
            ->when($this->filters['input_item'] ?? null, fn($q, $v) => $q->where('input_item', 'like', "%{$v}%"))
            ->orderBy('recipe')
            ->orderBy('output_item')
            ->orderBy('input_item')
            ->get();
    }

    public function headings(): array
    {
        return [
            'ID', 'Process', 'Output Item', 'Recipe', 'Output Item Description',
            'Output Item UOM', 'Batch Size', 'Output Item Location', 'Input Item',
            'Input Item Description', 'Input Item UOM', 'Input Item Qty Per',
            'Input Item Location', 'Process Code', 'No Series', 'Routing',
        ];
    }
}
