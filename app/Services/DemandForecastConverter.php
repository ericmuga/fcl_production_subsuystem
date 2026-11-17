<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class DemandForecastConverter
{
    public function convert(string $path, CarbonImmutable $monday, string $forecastName, string $location, string $component): Spreadsheet
    {
        $reader = new Xlsx();
        $reader->setReadDataOnly(true);
        $reader->setLoadSheetsOnly('Forecast By SKU');

        try {
            $source = $reader->load($path);
        } catch (ReaderException $e) {
            throw new InvalidArgumentException('Unable to read the Excel workbook. Please upload a valid .xlsx file.', 0, $e);
        }

        try {
            $sheet = $source->getSheetByName('Forecast By SKU');
            if (!$sheet) {
                throw new InvalidArgumentException('The workbook must contain a Forecast By SKU sheet.');
            }

            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            if (trim((string) $sheet->getCell('A3')->getValue()) !== 'No.'
                || trim((string) $sheet->getCell('B3')->getValue()) !== 'Description') {
                throw new InvalidArgumentException('The Forecast By SKU item headers do not match the expected format.');
            }
            foreach ($days as $index => $day) {
                $column = 4 + $index * 2;
                if (trim((string) $sheet->getCellByColumnAndRow($column, 2)->getValue()) !== $day
                    || trim((string) $sheet->getCellByColumnAndRow($column, 3)->getValue()) !== 'Vol Pcs') {
                    throw new InvalidArgumentException("The Forecast By SKU {$day} Vol Pcs column is missing or misplaced.");
                }
            }

            $output = new Spreadsheet();
            $out = $output->getActiveSheet();
            $out->setTitle('Demand Forecast Entries');
            $out->fromArray([[
                'Demand Forecast Name', 'Item No.', 'Description', 'Variant Code', 'Location Code',
                'Forecast Quantity (Base)', 'Forecast Date', 'Forecast Quantity', 'Unit of Measure Code', 'Component Forecast',
            ]], null, 'A1');
            $outputRow = 2;

            for ($row = 4; $row <= $sheet->getHighestDataRow(); $row++) {
                $item = trim((string) $sheet->getCell("A{$row}")->getValue());
                $description = trim((string) $sheet->getCell("B{$row}")->getValue());
                if (strcasecmp($item, 'Grand Total') === 0 && $description === '') {
                    continue;
                }
                $quantities = [];
                foreach ($days as $index => $day) {
                    $cell = $sheet->getCellByColumnAndRow(4 + $index * 2, $row);
                    $quantity = $cell->getDataType() === DataType::TYPE_FORMULA ? $cell->getOldCalculatedValue() : $cell->getValue();
                    if ($cell->getDataType() === DataType::TYPE_FORMULA && $quantity === null) {
                        throw new InvalidArgumentException("Row {$row}, {$day}: formula has no saved value. Recalculate and save the workbook in Excel.");
                    }
                    if ($quantity === null || $quantity === '') {
                        continue;
                    }
                    if (!is_numeric($quantity) || !is_finite((float) $quantity) || (float) $quantity < 0) {
                        throw new InvalidArgumentException("Row {$row}, {$day}: Vol Pcs must be a non-negative number.");
                    }
                    if ((float) $quantity > 0) {
                        $quantities[$index] = (float) $quantity;
                    }
                }
                if ($item === '' && $description === '' && !$quantities) {
                    continue;
                }
                if ($item === '' || $description === '' || $sheet->getCell("A{$row}")->getDataType() === DataType::TYPE_FORMULA
                    || $sheet->getCell("B{$row}")->getDataType() === DataType::TYPE_FORMULA) {
                    throw new InvalidArgumentException("Row {$row}: item number and description must be present as text.");
                }

                foreach ($quantities as $index => $quantity) {
                    foreach (['A' => $forecastName, 'B' => $item, 'C' => $description, 'E' => $location, 'I' => 'PC', 'J' => $component] as $column => $value) {
                        $out->setCellValueExplicit($column . $outputRow, $value, DataType::TYPE_STRING);
                    }
                    $out->setCellValue('G' . $outputRow, Date::PHPToExcel($monday->addDays($index)));
                    $out->setCellValue('H' . $outputRow, $quantity);
                    $outputRow++;
                }
            }
            if ($outputRow === 2) {
                throw new InvalidArgumentException('The Forecast By SKU sheet contains no positive Vol Pcs quantities.');
            }
            $out->getStyle('G2:G' . ($outputRow - 1))->getNumberFormat()->setFormatCode('yyyy-mm-dd');

            return $output;
        } finally {
            $source->disconnectWorksheets();
        }
    }
}
