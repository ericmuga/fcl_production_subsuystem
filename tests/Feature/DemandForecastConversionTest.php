<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class DemandForecastConversionTest extends TestCase
{
    private function workbook(array $changes = []): UploadedFile
    {
        $book = new Spreadsheet();
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('Forecast By SKU');
        $sheet->setCellValue('A3', 'No.');
        $sheet->setCellValue('B3', 'Description');
        foreach (['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $index => $day) {
            $sheet->setCellValueByColumnAndRow(4 + 2 * $index, 2, $day);
            $sheet->setCellValueByColumnAndRow(4 + 2 * $index, 3, 'Vol Pcs');
        }
        $sheet->setCellValue('A4', 'J123');
        $sheet->setCellValue('B4', 'Product');
        $sheet->setCellValue('D4', 2.5);
        $sheet->setCellValue('E4', 999);
        $sheet->setCellValue('F4', 0);
        $sheet->setCellValue('P4', 4);
        $sheet->setCellValue('A5', 'Grand Total');
        $sheet->setCellValue('D5', 100);
        foreach ($changes as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }
        $path = tempnam(sys_get_temp_dir(), 'forecast');
        try {
            (new Xlsx($book))->save($path);
            return UploadedFile::fake()->createWithContent('forecast.xlsx', file_get_contents($path));
        } finally {
            $book->disconnectWorksheets();
            unlink($path);
        }
    }

    private function fields(UploadedFile $file): array
    {
        return [
            'forecast_file' => $file,
            'forecast_monday' => '2026-11-16',
            'forecast_name' => 'FCL-FC2026',
            'location_code' => '03535',
            'component_forecast' => 'No',
        ];
    }

    public function test_download_maps_piece_quantities_and_dates_to_erp_columns()
    {
        $response = $this->actingAs(new User())->post('/planned-orders/demand-forecast', $this->fields($this->workbook()));
        $response->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $path = tempnam(sys_get_temp_dir(), 'forecast-result');
        try {
            file_put_contents($path, $response->streamedContent());
            $result = IOFactory::createReader('Xlsx')->load($path);
            $sheet = $result->getSheetByName('Demand Forecast Entries');
            $this->assertSame([
                'Demand Forecast Name', 'Item No.', 'Description', 'Variant Code', 'Location Code',
                'Forecast Quantity (Base)', 'Forecast Date', 'Forecast Quantity', 'Unit of Measure Code', 'Component Forecast',
            ], $sheet->rangeToArray('A1:J1')[0]);
            $this->assertSame(3, $sheet->getHighestDataRow());
            $this->assertSame(['FCL-FC2026', 'J123', 'Product', null, '03535', null], $sheet->rangeToArray('A2:F2')[0]);
            $this->assertSame('2026-11-16', Date::excelToDateTimeObject($sheet->getCell('G2')->getValue())->format('Y-m-d'));
            $this->assertEquals(2.5, $sheet->getCell('H2')->getValue());
            $this->assertSame('PC', $sheet->getCell('I2')->getValue());
            $this->assertSame('No', $sheet->getCell('J2')->getValue());
            $this->assertSame('2026-11-22', Date::excelToDateTimeObject($sheet->getCell('G3')->getValue())->format('Y-m-d'));
            $this->assertEquals(4, $sheet->getCell('H3')->getValue());
            $this->assertSame(DataType::TYPE_STRING, $sheet->getCell('E2')->getDataType());
        } finally {
            if (isset($result)) {
                $result->disconnectWorksheets();
            }
            unlink($path);
        }
    }

    public function test_rejects_bad_dates_headers_and_quantities()
    {
        $user = $this->actingAs(new User());
        $fields = $this->fields($this->workbook());
        $fields['forecast_monday'] = '2026-11-17';
        $user->post('/planned-orders/demand-forecast', $fields)->assertSessionHasErrors('forecast_monday');
        $user->post('/planned-orders/demand-forecast', $this->fields($this->workbook(['D2' => 'Tuesday'])))
            ->assertSessionHasErrors('forecast_file');
        $user->post('/planned-orders/demand-forecast', $this->fields($this->workbook(['D4' => -1])))
            ->assertSessionHasErrors('forecast_file');
    }

    public function test_uses_saved_formula_results()
    {
        $response = $this->actingAs(new User())->post('/planned-orders/demand-forecast', $this->fields($this->workbook(['D4' => '=1+2'])));
        $response->assertOk();
        $path = tempnam(sys_get_temp_dir(), 'forecast-result');
        try {
            file_put_contents($path, $response->streamedContent());
            $result = IOFactory::createReader('Xlsx')->load($path);
            $this->assertEquals(3, $result->getActiveSheet()->getCell('H2')->getValue());
        } finally {
            if (isset($result)) {
                $result->disconnectWorksheets();
            }
            unlink($path);
        }
    }

    public function test_requires_authentication()
    {
        $this->postJson('/planned-orders/demand-forecast', [])->assertUnauthorized();
    }
}
