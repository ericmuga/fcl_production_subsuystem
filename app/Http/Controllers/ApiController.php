<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ApiController extends Controller
{
    private function parseDates($from_date, $to_date)
    {
        return [
            'from_date' => Carbon::parse($from_date),
            'to_date' => Carbon::parse($to_date),
        ];
    }

    private function fetchData($table, $columns, $from_date, $to_date)
    {
        return DB::table($table . ' as a')
            ->whereDate('a.created_at', '>=', $from_date)
            ->whereDate('a.created_at', '<=', $to_date)
            ->select($columns)
            ->orderByDesc('a.created_at')
            ->get();
    }

    private function validateDateRange(Request $request)
    {
        return validator()->make($request->all(), [
            'from_date' => 'required|date|before_or_equal:to_date',
            'to_date' => 'required|date|after_or_equal:from_date',
        ]);
    }

    public function getSlaughterData(Request $request)
    {
        $validator = $this->validateDateRange($request);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dates = $this->parseDates($request->from_date, $request->to_date);

        $columns = [
            'receipt_no', 'slapmark', 'item_code', 'vendor_no', 'vendor_name', 
            'actual_weight', 'net_weight', 'meat_percent', 'settlement_weight', 
            'classification_code', 'created_at'
        ];

        $slaughter_data = $this->fetchData('slaughter_data', $columns, $dates['from_date'], $dates['to_date']);

        return response()->json($slaughter_data);
    }

    public function missingSlapData(Request $request)
    {
        $validator = $this->validateDateRange($request);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dates = $this->parseDates($request->from_date, $request->to_date);

        $columns = [
            'a.slapmark', 'a.item_code', 'a.actual_weight', 'a.net_weight', 'a.settlement_weight',
            'a.meat_percent', 'a.classification_code', 'a.created_at'
        ];

        $slaps_data = $this->fetchData('missing_slap_data', $columns, $dates['from_date'], $dates['to_date']);

        return response()->json($slaps_data);
    }

    public function getBeheadingData(Request $request)
    {
        $validator = $this->validateDateRange($request);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dates = $this->parseDates($request->from_date, $request->to_date);

        $columns = [
            'item_code', 'no_of_carcass', 'actual_weight', 'net_weight', 'process_code',
            'return_entry', 'a.created_at'
        ];

        $beheading_data = $this->fetchData('beheading_data', $columns, $dates['from_date'], $dates['to_date']);

        return response()->json($beheading_data);
    }

    public function getBrakingData(Request $request)
    {
        $validator = $this->validateDateRange($request);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dates = $this->parseDates($request->from_date, $request->to_date);

        $columns = [
            'carcass_type', 'item_code', 'actual_weight', 'net_weight', 'process_code',
            'product_type', 'no_of_items', 'created_at'
        ];

        $braking_data = $this->fetchData('butchery_data', $columns, $dates['from_date'], $dates['to_date']);

        return response()->json($braking_data);
    }

    public function getDeboningData(Request $request)
    {
        $validator = $this->validateDateRange($request);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dates = $this->parseDates($request->from_date, $request->to_date);

        $columns = [
            'item_code', 'actual_weight', 'net_weight', 'process_code', 'product_type',
            'no_of_pieces', 'no_of_crates', 'splitted', 'narration', 'created_at'
        ];

        $deboning_data = $this->fetchData('deboned_data', $columns, $dates['from_date'], $dates['to_date']);

        return response()->json($deboning_data);
    }

    public function getChoppingData(Request $request)
    {
        $validator = $this->validateDateRange($request);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation Error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $dates = $this->parseDates($request->from_date, $request->to_date);

        $columns = [
            'a.chopping_id', 'a.item_code', 'a.weight', 'a.output as is_output', 'a.created_at'
        ];

        $chopping_data = DB::table('chopping_lines as a')
            ->join('choppings as b', 'a.chopping_id', '=', 'b.chopping_id')
            ->where('b.status', '=', 1)
            ->whereDate('a.created_at', '>=', $dates['from_date'])
            ->whereDate('a.created_at', '<=', $dates['to_date'])
            ->select($columns)
            ->orderByDesc('a.chopping_id')
            ->get();

        return response()->json($chopping_data);
    }

    public function saveSlaughterReceipts(Request $request, Helpers $helpers)
    {
        // forgetCache data
        $helpers->forgetCache('lined_up');
        $helpers->forgetCache('weigh_receipts');
        $helpers->forgetCache('imported_receipts');

        // Validate the request data for an array of receipts
        $validator = Validator::make($request->all(), [
            '*.receipt_no' => 'required|string|max:20',
            '*.item_no' => 'required|string|max:20',
            '*.item_description' => 'required|string|max:100',
            '*.tag' => 'nullable|string|max:20',
            '*.vendor_no' => 'required|string|max:20',
            '*.vendor_name' => 'required|string|max:100',
            '*.receipt_date' => 'required|date',
            '*.qty' => 'required|numeric',
            '*.bin' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Loop through each receipt line in the request
            foreach ($request->all() as $receipt) {
                // Check if a record with the same enrolment_no and slaughter_date already exists
                $existingReceipt = DB::table('receipts')
                    ->where('enrolment_no', $receipt['receipt_no'])
                    ->where('slaughter_date', $receipt['slaughter_date'] ?? now())
                    ->first();

                if (!$existingReceipt) {
                    // Insert the receipt into the database only if it does not exist
                    DB::table('receipts')->insert([
                        'enrolment_no' => $receipt['receipt_no'],  // Assuming receipt_no is used as enrolment_no
                        'vendor_tag' => $receipt['tag'],
                        'receipt_no' => $receipt['receipt_no'],
                        'vendor_no' => $receipt['vendor_no'],
                        'vendor_name' => $receipt['vendor_name'],
                        'receipt_date' => $receipt['receipt_date'],
                        'item_code' => $receipt['item_no'],
                        'description' => $receipt['item_description'],
                        'received_qty' => $receipt['qty'],
                        'slaughter_date' => $receipt['slaughter_date'] ?? now(),  // Use slaughter_date or default to current date
                        'user_id' => null,  // Assuming user_id is 1 for now
                    ]);
                }
            }
            return response()->json(['success' => true, 'message' => 'Receipts created successfully', 'timestamp' => now()], 201);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => 'Failed to create receipts', 'details' => $e->getMessage(), 'timestamp' => now()], 500);
        }
    }

    public function pushSlaughterLines()
    {
        try {
            // Fetch data from the table
            $lines = DB::table('slaughter_data')
                // ->where('receipt_no', 'FRT-0000022259')
                ->where('created_at', today())
                ->select(
                    'receipt_no',
                    'slapmark',
                    'item_code',
                    'vendor_no',
                    'vendor_name',
                    'actual_weight',
                    'net_weight',
                    'meat_percent',
                    'settlement_weight',
                    'classification_code',
                    'created_at'  
                )
                ->get();

            // Build an array of JSON objects
            $payload = $lines->map(function($line) {
                return [
                    'receipt_no' => $line->receipt_no,
                    'slapmark' => $line->slapmark,
                    'item_code' => $line->item_code,
                    'vendor_no' => $line->vendor_no,
                    'vendor_name' => $line->vendor_name,
                    'actual_weight' => $line->actual_weight,
                    'net_weight' => $line->net_weight,
                    'meat_percent' => $line->meat_percent,
                    'settlement_weight' => $line->settlement_weight,
                    'classification_code' => $line->classification_code,
                    'created_at' => $line->created_at,
                ];
            });

            // Push the data to the external system
            // $response = Http::post('https://external-system-endpoint.com/api/v1/slaughter-lines', $payload->toArray());

            // // Handle the response from the external system
            // if ($response->successful()) {
            //     return response()->json(['message' => 'Data pushed successfully'], 200);
            // } else {
            //     return response()->json(['error' => 'Failed to push data', 'details' => $response->body()], 500);
            // }
            return $payload->toArray();

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to push slaughter lines', 'details' => $e->getMessage()], 500);
        }
    }
}
