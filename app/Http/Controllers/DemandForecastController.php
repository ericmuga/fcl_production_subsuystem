<?php

namespace App\Http\Controllers;

use App\Services\DemandForecastConverter;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DemandForecastController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function convert(Request $request, DemandForecastConverter $converter)
    {
        $data = $request->validate([
            'forecast_file' => 'required|file|mimes:xlsx|max:30720',
            'forecast_monday' => 'required|date_format:Y-m-d',
            'forecast_name' => 'required|string|max:100',
            'location_code' => 'required|string|max:20',
            'component_forecast' => ['required', Rule::in(['Yes', 'No'])],
        ]);
        $monday = CarbonImmutable::createFromFormat('!Y-m-d', $data['forecast_monday']);
        if (!$monday->isMonday()) {
            throw ValidationException::withMessages(['forecast_monday' => 'Select a Monday for the forecast week.']);
        }
        try {
            $output = $converter->convert($request->file('forecast_file')->getRealPath(), $monday, $data['forecast_name'], $data['location_code'], $data['component_forecast']);
        } catch (InvalidArgumentException $e) {
            throw ValidationException::withMessages(['forecast_file' => $e->getMessage()]);
        }

        return response()->streamDownload(function () use ($output) {
            try {
                (new Xlsx($output))->save('php://output');
            } finally {
                $output->disconnectWorksheets();
            }
        }, 'Demand Forecast Entries.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
