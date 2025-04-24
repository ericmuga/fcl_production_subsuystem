<?php

namespace App\Http\Controllers;

use App\Exports\ChoppingV2LinesExport;
use App\Exports\PostedChoppingLinesExport;
use App\Imports\RecipeImport;
use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;

class ChoppingController extends Controller
{
    // Define the item list as a class property
    private $itemList = [
        'G2103', 'G2107', 'G2109', 'G2110', 'G2111', 'G2113', 'G2114', 'G2115',
        'G2116', 'G2117', 'G2118', 'G2119', 'G2120', 'G2121', 'G2122', 'G2123',
        'G2125', 'G2126', 'G2127', 'G2128', 'G2129', 'G2130', 'G2131', 'G2132',
        'G2133', 'G2137', 'G2139', 'G2140', 'G2141', 'G2142', 'G2143', 'G2145',
        'G2146', 'G2147', 'G2148', 'G2151', 'G2157', 'G2158', 'G2162', 'G2165',
        'G2166', 'G2167', 'G2172', 'G2173', 'G2174', 'G2176'
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function batchLists(Helpers $helpers, $filter = null)
    {
        $title = "Chopping-Batches";

        $date_filter = today()->subDays(7);

        $templates = DB::table('template_header')
            ->where('template_lines.main_product', 'Yes')
            ->Join('template_lines', 'template_header.template_no', '=', 'template_lines.template_no')
            ->select('template_header.template_no', 'template_header.template_name', 'template_lines.description as template_output')
            ->get();

        $batches = DB::table('batches')
            ->where('batch_no', 'LIKE', 'ch-%')
            ->where('template_lines.main_product', 'Yes')
            ->whereDate('batches.created_at', '>=', today()->subDays(5)) //last 5 days
            ->leftJoin('users', 'batches.user_id', '=', 'users.id')
            ->leftJoin(
                'template_header',
                'batches.template_no',
                '=',
                'template_header.template_no'
            )
            ->leftJoin('template_lines', 'batches.template_no', '=', 'template_lines.template_no')
            ->select('batches.*', 'users.username', 'template_header.template_name', 'template_lines.description as template_output')
            ->when($filter == 'open' || $filter == '', function ($q) {
                $q->where('batches.status', '=', 'open'); // open batches
            })
            ->when($filter == 'posted', function ($q) {
                $q->where('batches.status', '=', 'posted'); // posted batches
            })
            ->when($filter == 'closed', function ($q) {
                $q->where('batches.status', '=', 'closed'); // closed batches
            })
            ->orderBy('batches.created_at', 'DESC')
            ->get();

        return view('chopping.batches', compact('title', 'filter', 'templates', 'batches', 'helpers', 'date_filter'));
    }

    public function choppingSaveBatch(Request $request, Helpers $helpers)
    {
        $temp_no = strtok($request->temp_no,  '-');
        $uniqueNumber = now()->format('YmdHis') . mt_rand(10, 99);
        $batch_no = 'ch-' . $uniqueNumber;

        try {
            //insert batch
            DB::transaction(function () use ($request, $helpers, $temp_no, $batch_no) {
                DB::table('batches')->insert([
                    'batch_no' => $batch_no,
                    'template_no' => $temp_no,
                    'output_quantity' => 0,
                    'status' => $request->status,
                    'from_batch' => $request->from_batch,
                    'user_id' => Auth::id(),
                ]);

                // get template lines
                $temp_lines = DB::table('template_lines')->where('template_no', $temp_no)
                    ->select('item_code', 'units_per_100')
                    ->get();

                if (!empty($temp_lines)) {
                    foreach ($temp_lines as $tl) {
                        DB::table('production_lines')->insert([
                            'batch_no' => $batch_no,
                            'item_code' => $tl->item_code,
                            'template_no' => $temp_no,
                            'quantity' => $tl->units_per_100,
                        ]);
                    }
                }
            });

            Toastr::success("Chopping Batch {$request->batch_no} created successfully", "Success");
            return redirect()
                ->route('chopping_batches_list', $request->status);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back();
        }
    }

    public function productionLines($batch_no, Helpers $helpers, $from_batch)
    {
        $title = "Chopping Production Lines";

        $table = 'production_lines';

        $lines = DB::table('production_lines')
            ->where('production_lines.batch_no', $batch_no)
            ->leftJoin('batches', 'production_lines.batch_no', '=', 'batches.batch_no')
            ->join('template_lines', function ($join) use ($table) {
                $join->on($table . '.item_code', '=',  'template_lines.item_code');
                $join->on($table . '.template_no', '=', 'template_lines.template_no');
            })
            ->select('production_lines.template_no', 'production_lines.*', 'template_lines.description', 'template_lines.percentage', 'batches.to_batch', 'batches.from_batch', 'template_lines.type', 'template_lines.main_product', 'template_lines.unit_measure', 'template_lines.units_per_100', 'template_lines.location', 'batches.status')
            ->orderBy('template_lines.type', 'ASC')
            ->get();

        return view('chopping.production-lines', compact('title', 'lines', 'helpers', 'batch_no', 'from_batch'));
    }

    public function postedLinesReport(Helpers $helpers, $filter = null)
    {
        $title = "Chopping Production Lines";

        $table = 'production_lines';

        $date_filter = 2;

        $lines = DB::table('production_lines')
            ->where('batches.status', 'posted')
            ->leftJoin('batches', 'production_lines.batch_no', '=', 'batches.batch_no')
            ->join('template_header', 'production_lines.template_no', '=', 'template_header.template_no')
            ->join('template_lines', function ($join) use ($table) {
                $join->on($table . '.item_code', '=',  'template_lines.item_code');
                $join->on($table . '.template_no', '=', 'template_lines.template_no');
            })
            ->when($filter == '', function ($q) use ($date_filter) {
                $q->whereDate('production_lines.created_at', '>=', today()->subDays((int)$date_filter)); // last 5 days
            })
            ->select('production_lines.*', 'template_header.template_name', 'template_lines.description', 'template_lines.percentage', 'template_lines.type', 'template_lines.main_product', 'template_lines.unit_measure', 'template_lines.units_per_100', 'template_lines.location', 'batches.from_batch', 'batches.to_batch', 'batches.status', 'batches.updated_at as batch_update_time')
            ->orderBy('batches.batch_no', 'ASC')
            ->get();

        return view('chopping.production-lines-report', compact('title', 'lines', 'helpers', 'date_filter'));
    }

    public function postedLinesReportExport(Request $request)
    {
        $title = "Chopping Production Lines";

        $table = 'production_lines';

        $from_date = Carbon::parse($request->from_date);
        $to_date = Carbon::parse($request->to_date);
        $ext = '.xlsx';

        $lines = DB::table('production_lines')
            ->where('batches.status', 'posted')
            ->whereDate('production_lines.created_at', '>=', $from_date)
            ->whereDate('production_lines.created_at', '<=', $to_date)
            ->leftJoin('batches', 'production_lines.batch_no', '=', 'batches.batch_no')
            ->join('template_header', 'production_lines.template_no', '=', 'template_header.template_no')
            ->join('template_lines', function ($join) use ($table) {
                $join->on($table . '.item_code', '=',  'template_lines.item_code');
                $join->on($table . '.template_no', '=', 'template_lines.template_no');
            })
            ->select('production_lines.batch_no', 'batches.template_no as recipe_no', 'production_lines.item_code', 'template_lines.description', 'template_header.template_name', 'template_lines.type', 'template_lines.main_product', 'template_lines.unit_measure', 'production_lines.quantity', DB::raw('(batches.to_batch - TRY_CAST(batches.from_batch AS DECIMAL)) + 1 as batch_size'), DB::raw('((batches.to_batch - TRY_CAST(batches.from_batch AS DECIMAL)) + 1 )*(production_lines.quantity) as total_qty_used'), 'batches.updated_at as batch_update_time')
            ->orderBy('batches.batch_no', 'ASC')
            ->get();

        $exports = Session::put('session_export_data', $lines);

        return Excel::download(new PostedChoppingLinesExport, "Posted Chopping Lines from- {$request->from_date} to {$request->to_date} $ext");
    }

    public function postedLinesReportSumm(Helpers $helpers, $filter = null)
    {
        $title = "Chopping Production Lines";

        $table = 'production_lines';

        $lines = DB::table('production_lines')
            ->where('batches.status', 'posted')
            ->leftJoin('batches', 'production_lines.batch_no', '=', 'batches.batch_no')
            ->join('template_header', 'production_lines.template_no', '=', 'template_header.template_no')
            ->join('template_lines', function ($join) use ($table) {
                $join->on($table . '.item_code', '=',  'template_lines.item_code');
                $join->on($table . '.template_no', '=', 'template_lines.template_no');
            })
            ->when($filter == '', function ($q) {
                $q->whereDate('production_lines.created_at', '>=', today()->subDays(5)); // last 5 days
            })
            ->select('production_lines.*', 'template_header.template_name', 'template_lines.description', 'template_lines.percentage', 'template_lines.type', 'template_lines.main_product', 'template_lines.unit_measure', 'template_lines.units_per_100', 'template_lines.location', 'batches.from_batch', 'batches.to_batch', 'batches.status')
            ->orderBy('batches.batch_no', 'ASC')
            ->get()->dd();

        return view('chopping.production-lines-report', compact('title', 'lines', 'helpers'));
    }

    public function postedLinesReportSummary(Helpers $helpers, $filter = null)
    {
        $title = "Chopping Production Lines";

        $table = 'production_lines';

        $lines = DB::table('production_lines')
            ->where('batches.status', 'posted')
            ->where('template_lines.type', 'Intake')
            // ->when($filter == 'today', function ($q) {
            //     $q->whereDate('production_lines.created_at', today()); // today
            // })
            ->leftJoin('batches', 'production_lines.batch_no', '=', 'batches.batch_no')
            ->join('template_lines', function ($join) use ($table) {
                $join->on($table . '.item_code', '=',  'template_lines.item_code');
                $join->on($table . '.template_no', '=', 'template_lines.template_no');
            })
            ->select(
                'batches.batch_no',
                'production_lines.item_code',
                'template_lines.description',
                'template_lines.type',
                'template_lines.main_product',
                'template_lines.unit_measure',
                'template_lines.location',
                'batches.to_batch',
                'batches.from_batch',
                DB::raw('SUM(production_lines.quantity) as used_quantity')
            )
            ->groupBy(
                'production_lines.item_code',
                'template_lines.description',
                'template_lines.type',
                'template_lines.main_product',
                'template_lines.unit_measure',
                'template_lines.location',
                'batches.to_batch',
                'batches.from_batch',
                'batches.batch_no'
            )
            ->orderBy('batches.batch_no')
            ->get()->dd();

        return view('chopping.production-summary-report', compact('title', 'lines', 'helpers'));
    }

    public function updateBatchItems(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                //update
                foreach ($request->item_code as $key => $value) {

                    DB::table('production_lines')
                        ->where('batch_no', $request->item_name)
                        ->where('item_code', $value)
                        ->update([
                            'quantity' => $request->qty[$key],
                            'updated_at' => now(),
                        ]);
                }

                DB::table('production_lines')
                    ->where('batch_no', $request->item_name)
                    ->where('item_code', $request->main_item)
                    ->update([
                        'quantity' => $request->total_output,
                        'updated_at' => now(),
                    ]);
            });

            Toastr::success("Update items on batch no: {$request->item_name} completed successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back();
        }
    }

    public function closeOrPostBatch(Request $request, Helpers $helpers)
    {
        try {
            $route_filter = 'closed';

            $to_batch = (int)$request->to_batch;
            $from_batch = (int)$request->from_batch;

            $batch_size = ($to_batch - $from_batch) + 1;

            switch ($request->filter) {
                case 'close':
                    //close batch
                    DB::table('batches')
                        ->where('batch_no', $request->batch_no)
                        ->update([
                            'from_batch' => $request->from_batch,
                            'to_batch' => $request->to_batch,
                            'status' => 'closed',
                            'output_quantity' => $this->getQuantityTotal($request->batch_no, $batch_size, $request->main_item),
                            'closed_by' => Auth::id(),
                            'updated_at' => now(),
                        ]);
                    break;

                case 'post':
                    $route_filter = 'posted';
                    //post batch
                    DB::transaction(
                        function () use ($request, $helpers) {
                            //update batch to posted
                            DB::table('batches')
                                ->where('batch_no', $request->batch_no)
                                ->update([
                                    'status' => 'posted',
                                    'posted_by' => Auth::id(),
                                    'updated_at' => now(),
                                ]);
                        }
                    );
                    break;

                default:
                    # code...
                    break;
            }

            Toastr::success("Action {$request->filter} batch no: {$request->item_name} completed successfully", 'Success');
            return redirect()->route('chopping_batches_list', $route_filter);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back();
        }
    }

    private function getQuantityTotal($batch_no, $batch_size, $main_item)
    {
        $sum_qty = DB::table('production_lines')
            ->where('batch_no', $batch_no)
            ->where('item_code', '!=', $main_item)
            ->select(DB::raw('SUM(quantity * ' . (int)$batch_size . ') as total_qty'))
            ->value('total_qty');

        return $sum_qty;
    }

    public function weigh(Helpers $helpers, $filter = null)
    {
        $title = "Chopping-V2";

        $date_filter = today()->subDays(7);

        $templates = DB::table('template_header')
            ->where('template_lines.main_product', 'Yes')
            ->Join('template_lines', 'template_header.template_no', '=', 'template_lines.template_no')
            ->select('template_header.template_no', 'template_header.template_name', 'template_lines.description as template_output')
            ->get();

        $scale_configs = DB::table('scale_configs')
            ->where('section', 'chopping')
            ->select('scale', 'comport', 'tareweight', 'ip_address')
            ->get()
            ->keyBy('scale')
            ->toArray();

        $choppings = DB::table('choppings as a')
            ->join('users as b', 'a.user_id', '=', 'b.id')
            ->join('template_header as c', function ($join) {
                $join->on(DB::raw("LEFT(a.chopping_id, CHARINDEX('-', a.chopping_id + '-') - 1)"), '=', 'c.template_no');
            })
            ->leftJoin('users as d', 'a.closed_by', '=', 'd.id') // Join with users table again for closed_by
            ->where('a.status', 1)
            ->whereDate('a.created_at', today())
            ->select('a.*', 'b.username as creator_username', 'c.template_name', 'd.username as closer_username') // Select the username for the closer
            ->orderByDesc('a.id')
            ->get();

        return view('chopping.weigh', compact('title', 'templates', 'choppings', 'helpers', 'scale_configs'));
    }

    public function makeChoppingRun(Request $request, Helpers $helpers)
    {
        // Validate the request data if necessary
        $validatedData = $request->validate([
            // 'field_name' => 'validation_rules',
        ]);

        try {
            $templateNo = $request->input('template_no');

            // Get the count of existing runs for the same template_no and date
            $count = DB::table('choppings')
                        ->where('chopping_id', 'LIKE', $templateNo . '%')
                        ->whereDate('created_at', today())
                        ->count();

            // Calculate the next incremental number
            $incrementalNumber = $count + 1;
            $choppingId = $templateNo .'-'.$incrementalNumber;

            // Create a new ChoppingRun entry
            $insert = DB::table('choppings')->insert([
                'chopping_id' => $choppingId,
                'user_id' => Auth::id(),
            ]);

            // Return a JSON response indicating success
            return response()->json([
                'success' => true,
                'data' => $choppingId,
                'message' => 'Chopping run started successfully!',
            ], 200);
        } catch (\Exception $e) {
            // Return a JSON response indicating failure
            return response()->json([
                'success' => false,
                'message' => 'Failed to start chopping run!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchOpenRuns(Request $request)
    {
        $templateNo = $request->input('template_no');

        $todayStart = today();
        $tomorrowStart = $todayStart->copy()->addDay();
        $allowanceEnd = $tomorrowStart->copy()->addMinutes(30);

        $runs = DB::table('choppings')
                    ->where('status', 0)
                    ->whereBetween('created_at', [$todayStart, $allowanceEnd])
                    ->where('chopping_id', 'LIKE', $templateNo . '%')
                    ->select('chopping_id')
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $runs
        ]);        
    }

    public function fetchTemplateProducts(Request $request)
    {
        $templateNo = $request->input('template_no');
        $cacheKey = 'template_lines_' . $templateNo;

        $products = DB::table('template_lines')
            ->where('template_no', $templateNo)
            ->where('item_code', 'LIKE', 'G%')
            ->where('type', 'Intake')
            ->whereNotIn('item_code', $this->itemList) // Use class property
            ->select('item_code', 'description', 'type')
            ->get();

        // info($this->itemList);

        return response()->json([
            'success' => true,
            'data' => $products
        ]);        
    }

    public function saveChoppingWeights(Request $request)
    {
        try {
            //code...
            $insert = DB::table('chopping_lines')->insert([
                        'chopping_id' => $request->batch,
                        'item_code' => $request->product,
                        'weight' => $request->net,
                    ]);

            return response()->json([
                'success' => true,
                'data' => $request->batch,
                'reading' => $request->reading,
                'message' => 'Chopping weight inserted successfully!',
            ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to insert chopping run item!',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function closeChoppingRun(Request $request, Helpers $helpers)
    {
        try {
            $todayStart = Carbon::today();
            $allowanceEnd = $todayStart->copy()->addDay()->addMinutes(30);

            DB::transaction(function () use ($request, $helpers, $todayStart, $allowanceEnd) {
                $this->lockChoppingRecord($request, $todayStart, $allowanceEnd);
                $chopping_id = $this->getChoppingId($request->complete_run_number);

                $choppingLines = $this->getChoppingLines($request, $chopping_id);
                $this->insertChoppingLines($choppingLines);

                $this->insertOutputItem($request, $chopping_id, $todayStart, $allowanceEnd);
            });

            // $this->publishChoppingLines($request, $helpers, $todayStart, $allowanceEnd);

            Toastr::success("Chopping Run {$request->complete_run_number} closed successfully", "Success");
            return redirect()->route('chopping_weigh');
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            info($e->getMessage());
            return back();
        }
    }

    private function lockChoppingRecord($request, $todayStart, $allowanceEnd)
    {
        DB::table('choppings')
            ->where('chopping_id', $request->complete_run_number)
            ->whereBetween('created_at', [$todayStart, $allowanceEnd])
            ->lockForUpdate()
            ->update([
                'status' => 1,
                'closed_by' => Auth::id(),
                'updated_at' => now()
            ]);
    }

    private function getChoppingId($completeRunNumber)
    {
        $parts = explode('-', $completeRunNumber);
        return $parts[0];
    }

    private function getChoppingLines($request, $chopping_id)
    {
        // Use the class property for item list
        $item_list = $this->itemList;

        $bagItemCodes = ['H221053', 'H221016', 'H221187', 'H221015'];

        $spices = DB::table('template_lines')
            ->where(function ($query) use ($chopping_id, $item_list) {
                $query->where('item_code', 'like', 'H%')
                    ->orWhereIn('item_code', $item_list);
            })
            ->where('template_no', $chopping_id)
            ->whereNotIn('item_code', $bagItemCodes)
            ->select('item_code', 'units_per_100')
            ->get();

        $choppingLines = [];
        $processedItems = []; // Track processed item codes

        foreach ($spices as $sp) {
            if (!in_array($sp->item_code, $processedItems)) {
                $weight = ((float)$sp->units_per_100 / (float)$request->batch_size) * 2;
                $choppingLines[] = [
                    'chopping_id' => $request->complete_run_number,
                    'item_code' => $sp->item_code,
                    'weight' => $weight,
                ];
                $processedItems[] = $sp->item_code; // Mark item as processed
            } else {
                info("Duplicate item code detected: {$sp->item_code} for chopping_id: {$request->complete_run_number}");
            }
        }

        $waterValues = $this->getWaterValues();
        $waterItemCodes = $this->getWaterItemCodes();

        $waterInserted = false;
        $iceInserted = false;

        foreach ($waterValues as $recipeNo => $water) {
            if ($chopping_id == $recipeNo) {
                if (!$waterInserted && $waterItemCodes[$recipeNo] == 'G8900') {
                    $choppingLines[] = [
                        'chopping_id' => $request->complete_run_number,
                        'item_code' => 'G8900',
                        'weight' => ($water / (float)$request->batch_size) * 2,
                    ];
                    $waterInserted = true;
                } elseif (!$iceInserted && $waterItemCodes[$recipeNo] == 'G8901') {
                    $choppingLines[] = [
                        'chopping_id' => $request->complete_run_number,
                        'item_code' => 'G8901',
                        'weight' => ($water / (float)$request->batch_size) * 2,
                    ];
                    $iceInserted = true;
                }
            }
        }

        // if (in_array($chopping_id, array_keys($waterValues))) {
        //     $water = $waterValues[$chopping_id];
        //     if (!$waterInserted) {
        //         $choppingLines[] = [
        //             'chopping_id' => $request->complete_run_number,
        //             'item_code' => 'G8900',
        //             'weight' => ($water / (float)$request->batch_size) * 2,
        //         ];
        //     }
        // }

        return $choppingLines;
    }

    private function insertChoppingLines($choppingLines)
    {
        if (!empty($choppingLines)) {
            DB::table('chopping_lines')->insert($choppingLines);
        }
    }

    private function insertOutputItem($request, $chopping_id, $todayStart, $allowanceEnd)
    {
        $output = DB::table('template_lines')
            ->where('type', 'Output')
            ->where('template_no', $chopping_id)
            ->first();

        if ($output) {
            $totalInsertedWeight = DB::table('chopping_lines')
                ->where('chopping_id', $request->complete_run_number)
                ->whereNotIn('item_code', ['H221053', 'H221016', 'H221187'])
                ->whereBetween('created_at', [$todayStart, $allowanceEnd])
                ->sum('weight');

            DB::table('chopping_lines')->insert([
                'chopping_id' => $request->complete_run_number,
                'item_code' => $output->item_code,
                'weight' => $totalInsertedWeight,
                'output' => 1
            ]);
        }
    }

    private function publishChoppingLines($request, $helpers, $todayStart, $allowanceEnd)
    {
        $savedLines = DB::table('chopping_lines')
            ->where('chopping_id', $request->complete_run_number)
            ->whereBetween('created_at', [$todayStart, $allowanceEnd])
            ->get();

        $savedLinesArray = $savedLines->map(function ($line) {
            $line->timestamp = now()->toDateTimeString();
            return $line;
        })->toArray();

        //$helpers->publishToQueue($savedLinesArray, 'production_data_order_chopping.bc');
    }

    private function getWaterValues()
    {
        return [
            '1230G42' => 27, '1230G43' => 27, '1230J39' => 5, '1230J45' => 5, '1230J46' => 8,
            '1230J63' => 5, '1230J72' => 5, '1230J83' => 7, '1230J84' => 7, '1230J86' => 6.5,
            '1230J93' => 5, '1230K01' => 5, '1230K09' => 7.5, '1230K19' => 7, '1230K31' => 10.5,
            '1230K37' => 8.5, '1230K38' => 8.5, '1230K41' => 8.5, '1230K54' => 5, '1230K71' => 5,
            '1230K72' => 9, '1230K95' => 8.5, '1230K96' => 8.5, '1230L03' => 7, '1230L83' => 10,
            '1230L84' => 10, '1230M26' => 7, '1230M31' => 4, '1230M32' => 4, '1230M33' => 14,
            '1230M46' => 5, '1230M48' => 12, '1230M56' => 11, '1230M62' => 6, '1230M66' => 12,
            '1230M74' => 10.5, '1230M76' => 10.5, '1230J07' => 5, '1230J87' => 10, '1230J88' => 10,
            '1230K56' => 7.5, '1230L09' => 10, '1230L10' => 10, '1230L25' => 10, '1230M52' => 10,
        ];
    }

    private function getWaterItemCodes()
    {
        return [
            '1230G42' => 'G8900', '1230G43' => 'G8900', '1230J39' => 'G8900', '1230J45' => 'G8900',
            '1230J46' => 'G8900', '1230J63' => 'G8900', '1230J72' => 'G8900', '1230J83' => 'G8900',
            '1230J84' => 'G8900', '1230J86' => 'G8900', '1230J93' => 'G8900', '1230K01' => 'G8900',
            '1230K09' => 'G8900', '1230K19' => 'G8900', '1230K31' => 'G8900', '1230K37' => 'G8900',
            '1230K38' => 'G8900', '1230K41' => 'G8900', '1230K54' => 'G8900', '1230K71' => 'G8900',
            '1230K72' => 'G8900', '1230K95' => 'G8900', '1230K96' => 'G8900', '1230L03' => 'G8900',
            '1230L83' => 'G8900', '1230L84' => 'G8900', '1230M26' => 'G8900', '1230M31' => 'G8900',
            '1230M32' => 'G8900', '1230M33' => 'G8900', '1230M46' => 'G8900', '1230M48' => 'G8900',
            '1230M56' => 'G8900', '1230M62' => 'G8900', '1230M66' => 'G8900', '1230M74' => 'G8900',
            '1230M76' => 'G8900', '1230J07' => 'G8901', '1230J87' => 'G8901', '1230J88' => 'G8901',
            '1230K56' => 'G8901', '1230L09' => 'G8901', '1230L10' => 'G8901', '1230L25' => 'G8901',
            '1230M52' => 'G8901',
        ];
    }

    public function choppingLines($run_no)
    {
        $title = "Chopping Run Lines";

        $lines = DB::table('chopping_lines as a')
            ->leftJoin('template_lines as b', function($join) {
                $join->on('a.item_code', '=', 'b.item_code')
                     ->whereRaw('b.id = (SELECT TOP 1 id FROM template_lines WHERE item_code = b.item_code)');
            })
            ->where('a.chopping_id', $run_no)
            ->whereDate('a.created_at', today())
            ->select('a.*', 'b.description')
            ->orderBy('a.id', 'asc')
            ->get();

        return view('chopping.weigh-lines', compact('title', 'lines', 'run_no'));
    }

    public function choppingLinesReport(Request $request)
    {
        $title = 'Chopping Lines report';

        $lines = DB::table('chopping_lines as a')
            ->leftJoin('template_lines as b', function($join) {
                $join->on('a.item_code', '=', 'b.item_code')
                    ->whereRaw('b.id = (SELECT TOP 1 id FROM template_lines WHERE item_code = b.item_code)');
            })
            ->leftJoin(DB::raw('(SELECT id, template_no, template_name FROM template_header) as c'), function($join) {
                $join->on(DB::raw("LEFT(a.chopping_id, CHARINDEX('-', a.chopping_id) - 1)"), '=', 'c.template_no');
            })
            ->whereDate('a.created_at', today()) // today
            ->select('a.*', 'b.description', 'c.template_name')
            ->orderBy('a.id', 'asc')
            ->get();

        return view('chopping.lines-report', compact('title', 'lines'));
    }

    public function choppingLinesV2Export(Request $request)
    {
        $from_date = Carbon::parse($request->from_date);
        $to_date = Carbon::parse($request->to_date);
        $ext = '.xlsx';

        $lines = DB::table('chopping_lines as a')
            ->leftJoin('template_lines as b', function($join) {
                $join->on('a.item_code', '=', 'b.item_code')
                    ->whereRaw('b.id = (SELECT TOP 1 id FROM template_lines WHERE item_code = b.item_code)');
            })
            ->leftJoin(DB::raw('(SELECT id, template_no, template_name FROM template_header) as c'), function($join) {
                $join->on(DB::raw("LEFT(a.chopping_id, CHARINDEX('-', a.chopping_id) - 1)"), '=', 'c.template_no');
            })
            ->whereDate('a.created_at', '>=', $from_date)
            ->whereDate('a.created_at', '<=', $to_date)
            ->select(
                'a.chopping_id', 
                'c.template_name', 
                'a.item_code', 
                'b.description', 
                DB::raw("CASE WHEN a.output = 1 THEN 'Output' ELSE 'Input' END as output_type"),
                'a.weight', 
                'a.batch_no', 
                'a.created_at'
            )
            ->orderBy('a.chopping_id', 'asc')
            ->get();

        $exports = Session::put('session_export_data', $lines);

        return Excel::download(new ChoppingV2LinesExport, "Chopping Lines v2 from- {$request->from_date} to {$request->to_date} $ext");
    }

    public function getRecipeData(Request $request)
    {
        $title = 'Recipe data';

        $cacheKey = 'recipe_data_cache';
        $lines = Cache::remember($cacheKey, now()->addHours(10), function () {
            return DB::table('RecipeData as a')
            ->orderBy('a.recipe', 'asc')
            ->get();
        });

        return view('chopping.recipe-data', compact('title', 'lines'));
    }

    public function getProductionData(Request $request)
    {
        $title = 'Production data';

        $filter = 10; // last 2 days

        $lines = DB::table('ProductionData as a')
            ->orderBy('a.ProductionOrderNo', 'asc')
            ->whereDate('TransactionDate', '>=', today()->subDays((int)$filter)) // last 2 days
            ->get();

        return view('chopping.production-data', compact('title', 'lines', 'filter'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv',
        ]);
        // Forget cached data with key recipe_data_cache
        Cache::forget('recipe_data_cache');

        try {
            Excel::import(new RecipeImport, $request->file('excel_file'));
            Toastr::success("Excel file uploaded successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return redirect()->back();
        }
    }
}
