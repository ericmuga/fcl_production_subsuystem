<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ApiController extends Controller
{
    // defaults for IDTs posted by the sales portal on QA of returns
    private const RETURNS_IDT_LOCATIONS = ['1570', '2595', '2500', '2055', '3035', '4300', '4450', '3535', '3600', '4400'];
    private const RETURNS_IDT_TRANSFER_FROM = '3535';
    private const RETURNS_IDT_CHILLER_CODE = 'V';
    private const RETURNS_IDT_USER_ID = 1;

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

    /**
     * Create an IDT from a returned product QA'd on the sales portal.
     * PC items: quantity is pieces, weight is calculated. KG items: quantity is the net weight keyed in (no tare).
     */
    public function issueReturnIdt(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'external_ref' => 'required|string|max:64',
            'source_ref' => 'nullable|string|max:64',
            'product_code' => 'required|string|max:50',
            'location_code' => ['required', 'string', Rule::in(self::RETURNS_IDT_LOCATIONS)],
            'quantity' => 'required|numeric|gt:0',
            'batch_no' => 'required|string|max:50',
            'unit_of_measure' => 'nullable|string|max:10',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        // external_ref is the return reference no shared by all its lines; source_ref identifies this one transfer
        $externalRef = trim($request->external_ref);
        $sourceRef = $request->filled('source_ref') ? trim($request->source_ref) : $externalRef;

        // already posted (e.g. a retry after a timeout) - return the existing transfer
        $existing = DB::table('idt_transfers')->where('source_ref', $sourceRef)->first();
        if ($existing) {
            return response()->json([
                'success' => true,
                'duplicate' => true,
                'transfer_id' => $existing->id,
                'total_pieces' => $existing->total_pieces,
                'total_weight' => $existing->total_weight,
            ]);
        }

        $productCode = trim($request->product_code);
        $product = $this->findReturnIdtProduct($productCode);

        if (!$product) {
            return response()->json(['success' => false, 'message' => "Product {$productCode} not found in Calibra"], 422);
        }

        $quantity = (float) $request->quantity;
        $unitMeasure = strtoupper(trim((string) ($product->unit_of_measure ?: 'KG')));

        // the sender decides whether quantity is pieces or kgs from its own unit of measure - they must agree
        if ($request->filled('unit_of_measure')) {
            $senderIsKg = strtoupper(trim($request->unit_of_measure)) === 'KG';
            if ($senderIsKg !== ($unitMeasure === 'KG')) {
                return response()->json(['success' => false, 'message' => "Unit of measure mismatch for {$productCode}: Calibra has {$unitMeasure}, sent {$request->unit_of_measure}"], 422);
            }
        }

        if ($unitMeasure === 'KG') {
            $pieces = 0;
            $weight = round($quantity, 2);
        } else {
            $weightPerUnit = (float) $product->qty_per_unit_of_measure;
            if ($weightPerUnit <= 0) {
                return response()->json(['success' => false, 'message' => "Product {$productCode} has no weight per unit set in Calibra"], 422);
            }
            $pieces = (int) round($quantity);
            $weight = round($quantity * $weightPerUnit, 2);
        }

        $locationCode = $request->location_code;

        $data = [
            'product_code' => $productCode,
            'location_code' => $locationCode,
            'chiller_code' => self::RETURNS_IDT_CHILLER_CODE,
            'total_pieces' => $pieces,
            'total_weight' => $weight,
            'transfer_type' => 0,
            'transfer_from' => self::RETURNS_IDT_TRANSFER_FROM,
            'description' => $request->description,
            'batch_no' => trim($request->batch_no),
            'user_id' => self::RETURNS_IDT_USER_ID,
            'requires_approval' => (substr($productCode, 0, 1) === 'J') && ($locationCode !== '4450'),
            // QA already decided on the portal, no further approval on calibra
            'approved' => 1,
            'approved_by' => self::RETURNS_IDT_USER_ID,
            'external_ref' => $externalRef,
            'source_ref' => $sourceRef,
        ];

        // kitchen and incinerator are received on issue, same as despatch issue
        if ($locationCode == '4400' || $locationCode == '4300') {
            $data['receiver_total_pieces'] = $pieces;
            $data['receiver_total_weight'] = $weight;
            $data['received_by'] = self::RETURNS_IDT_USER_ID;
            $data['with_variance'] = 1;
        }

        try {
            $id = DB::table('idt_transfers')->insertGetId($data);
        } catch (\Exception $e) {
            Log::error('Exception in ' . __METHOD__ . '(): ' . $e->getMessage(), ['source_ref' => $sourceRef]);
            return response()->json(['success' => false, 'message' => 'Failed to save transfer'], 500);
        }

        return response()->json([
            'success' => true,
            'duplicate' => false,
            'transfer_id' => $id,
            'total_pieces' => $pieces,
            'total_weight' => $weight,
        ]);
    }

    /**
     * Unit of measure for products the sales portal is about to transfer, so it knows whether to ask QA for pieces or kgs.
     */
    public function returnIdtProducts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'codes' => 'required|array|max:500',
            'codes.*' => 'required|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'message' => $validator->errors()->first()], 422);
        }

        $products = [];
        foreach (array_unique(array_map('trim', $request->codes)) as $code) {
            $product = $this->findReturnIdtProduct($code);
            if ($product) {
                $products[$code] = [
                    'unit_of_measure' => strtoupper(trim((string) ($product->unit_of_measure ?: 'KG'))),
                    'qty_per_unit_of_measure' => (float) $product->qty_per_unit_of_measure,
                ];
            }
        }

        return response()->json(['success' => true, 'products' => $products]);
    }

    private function findReturnIdtProduct(string $productCode)
    {
        $item = DB::table('items')
            ->where(DB::raw('TRIM(code)'), $productCode)
            ->select('unit_of_measure', 'qty_per_unit_of_measure')
            ->first();

        if ($item) {
            return $item;
        }

        // imported products and beef/lamb items are weighed (KG)
        return DB::table('products')
            ->where(DB::raw('TRIM(code)'), $productCode)
            ->select('unit_of_measure', DB::raw('0 as qty_per_unit_of_measure'))
            ->first()
            ?: DB::table('beef_lamb_items')
                ->where('code', $productCode)
                ->select('unit_of_measure', DB::raw('1 as qty_per_unit_of_measure'))
                ->first();
    }
}
