<?php

namespace App\Http\Controllers;

use App\Exports\BeheadedCombinedExport;
use App\Exports\BreakingCombinedExport;
use App\Exports\DebonedCombinedExport;
use App\Models\BeheadingData;
use App\Models\ButcheryData;
use App\Models\Helpers;
use App\Models\Product;
use App\Models\Sale;
use App\Models\DebonedData;
use App\Models\SlaughterData;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ButcheryController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check');
    }

    public function index(Helpers $helpers)
    {
        $title = "dashboard";

        $baconers = DB::table('beheading_data')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', "G1030")
            ->sum('no_of_carcass');

        $sows = DB::table('beheading_data')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', "G1031")
            ->sum('no_of_carcass');

        $baconers_weight = DB::table('beheading_data')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', "G1030")
            ->sum('net_weight');

        $sows_weight = DB::table('beheading_data')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', "G1031")
            ->sum('net_weight');

        $lined_baconers = Cache::remember('lined_baconers', now()->addMinutes(360), function () use ($helpers) {

            $record1 = DB::table('slaughter_data')
                ->where('item_code', 'G0110')
                ->whereDate('created_at', $helpers->getButcheryDate())
                ->count();

            $record2 = DB::table('missing_slap_data')
                ->where('item_code', 'G0110')
                ->whereDate('created_at', $helpers->getButcheryDate())
                ->count();

            return $record1 + $record2;
        });

        $lined_sows = Cache::remember('linedup_sows', now()->addMinutes(360), function () use ($helpers) {

            $record1 = DB::table('slaughter_data')
                ->where('item_code', 'G0111')
                ->whereDate('created_at', $helpers->getButcheryDate())
                ->count();

            $record2 = DB::table('missing_slap_data')
                ->where('item_code', 'G0111')
                ->whereDate('created_at', $helpers->getButcheryDate())
                ->count();

            return $record1 + $record2;
        });

        $three_parts_baconers = DB::table('butchery_data')
            ->where('carcass_type', 'G1030')
            ->whereDate('created_at', Carbon::today())
            ->sum('net_weight');

        $three_parts_sows = DB::table('butchery_data')
            ->where('carcass_type', 'G1031')
            ->whereDate('created_at', Carbon::today())
            ->sum('net_weight');

        $b_legs = DB::table('butchery_data')
            ->where('carcass_type', 'G1030')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1100')
            ->sum('net_weight');

        $b_shoulders = DB::table('butchery_data')
            ->where('carcass_type', 'G1030')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1101')
            ->sum('net_weight');

        $b_middles = DB::table('butchery_data')
            ->where('carcass_type', 'G1030')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1102')
            ->sum('net_weight');

        $s_legs = DB::table('butchery_data')
            ->where('carcass_type', 'G1031')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1108')
            ->sum('net_weight');

        $s_shoulders = DB::table('butchery_data')
            ->where('carcass_type', 'G1031')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1109')
            ->sum('net_weight');

        $s_middles = DB::table('butchery_data')
            ->where('carcass_type', 'G1031')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1110')
            ->sum('net_weight');

        $sales = DB::table('sales')
            ->whereDate('created_at', Carbon::today())
            ->select(DB::raw('SUM(sales.net_weight) as total_net'), DB::raw('SUM(sales.no_of_carcass) as count'))
            ->get()->toArray();

        $slaughtered_baconers_weight = Cache::remember('slaughtered_baconers_weight', now()->addMinutes(360), function () use ($helpers) {

            return DB::table('slaughter_data')
                ->whereDate('created_at', $helpers->getButcheryDate())
                ->where('item_code', 'G0110')
                ->sum('net_weight');
        });

        $slaughtered_sows_weight = Cache::remember('slaughtered_sows_weight', now()->addMinutes(360), function () use ($helpers) {

            return DB::table('slaughter_data')
                ->whereDate('created_at', $helpers->getButcheryDate())
                ->where('item_code', 'G0111')
                ->sum('net_weight');
        });

        return view('butchery.dashboard', compact('title', 'baconers', 'sows', 'baconers_weight', 'sows_weight', 'lined_baconers', 'lined_sows', 'three_parts_baconers', 'three_parts_sows', 'helpers', 'b_legs', 'b_shoulders', 'b_middles', 's_legs', 's_shoulders', 's_middles', 'sales', 'slaughtered_baconers_weight', 'slaughtered_sows_weight'));
    }

    public function dashboardv2(Helpers $helpers)
    {
        $title = "dashboard-V2";

        $scale2_data = DB::table('butchery_data')
            ->whereDate('created_at', today())
            ->select(DB::raw('COALESCE(SUM(net_weight), 0) AS total_net'))
            ->whereIn('item_code', ['G1100', 'G1101', 'G1102'])
            ->groupBy('item_code')
            ->orderBy('item_code')
            ->pluck('total_net')
            ->toArray();

        $fat_stripping_total = DB::table('deboned_data')
            ->whereDate('created_at', today())
            ->where('process_code', 10)
            ->sum('net_weight');

        $main_items = DB::table('deboned_data')
            ->join('products', 'deboned_data.item_code', '=', 'products.code')
            ->whereDate('deboned_data.created_at', today())
            ->whereIn('deboned_data.product_type', [1, 2]) //main & by products 
            ->select('deboned_data.item_code', 'deboned_data.product_type', 'products.description', 'deboned_data.process_code', DB::raw('SUM(deboned_data.net_weight) as total_net'), DB::raw('SUM(deboned_data.no_of_pieces) as total_pieces'))
            ->selectRaw("CASE WHEN deboned_data.product_type = 1 THEN 'Main' ELSE 'By Product' END as product_type_name")
            ->groupBy('deboned_data.item_code', 'deboned_data.product_type', 'products.description', 'deboned_data.process_code')
            ->orderBy('total_net', 'DESC')
            ->get()->toArray();

        $process_filter = ['4', '7', '11', '5', '16', '17', '6', '18', '12'];

        $cumm = DB::table('deboned_data')
            ->whereDate('deboned_data.created_at', today())
            ->whereIn('deboned_data.process_code', $process_filter)
            ->whereIn('deboned_data.product_type', [1, 2])
            ->select(DB::raw('COALESCE(SUM(deboned_data.net_weight),0) as total_net'), DB::raw('COALESCE(SUM(deboned_data.no_of_pieces),0) as total_pieces'))
            ->get()->toArray();

        return view('butchery.dashboard2', compact('title', 'main_items', 'cumm', 'helpers', 'scale2_data', 'fat_stripping_total'));
    }

    public function scaleOneAndTwo(Helpers $helpers)
    {
        $title = "Scale-1&2";

        $configs = Cache::remember('scale12_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('section', 'butchery')
                ->where('scale', 'Beheading')
                ->orWhere('scale', 'Breaking')
                ->select('scale', 'tareweight', 'comport')
                ->get()->groupBy('scale');
        });

        $products = Cache::remember('products_scale12', now()->addMinutes(120), function () {
            return DB::table('products')
                ->orWhere('code', 'G1100')
                ->orWhere('code', 'G1101')
                ->orWhere('code', 'G1102')
                ->orWhere('code', 'G1108')
                ->orWhere('code', 'G1109')
                ->orWhere('code', 'G1110')
                ->orderBy('code', 'ASC')
                ->get();
        });

        $beheading_data = DB::table('beheading_data')
            ->whereDate('beheading_data.created_at', Carbon::today())
            ->leftJoin('products', 'beheading_data.item_code', '=', 'products.code')
            ->select('beheading_data.*', 'products.description')
            ->orderBy('beheading_data.created_at', 'DESC')
            ->get();

        $butchery_data = DB::table('butchery_data')
            ->whereDate('butchery_data.created_at', Carbon::today())
            ->leftJoin('products', 'butchery_data.item_code', '=', 'products.code')
            ->select('butchery_data.*', 'products.description')
            ->orderBy('butchery_data.created_at', 'DESC')
            ->get();

        return view('butchery.scale1-2', compact('title', 'configs', 'products', 'beheading_data', 'butchery_data', 'helpers'));
    }

    public function readScaleApiService(Request $request, Helpers $helpers)
    {
        $result = $helpers->get_scale_read($request->comport);
        return response()->json($result);
    }

    public function comportlistApiService(Helpers $helpers)
    {
        $result = $helpers->get_comport_list();

        return response()->json($result);
    }

    public function saveScaleOneData(Request $request, Helpers $helpers)
    {
        try {
            $userId = $helpers->authenticatedUserId();
            $carcassType = $request->carcass_type;
            $noOfCarcass = $request->no_of_carcass;
            $actualWeight = $request->reading;
            $netWeight = $request->net;

            // Define base data for insertion
            $baseData = [
                'item_code' => $carcassType,
                'no_of_carcass' => $noOfCarcass,
                'actual_weight' => $actualWeight,
                'net_weight' => $netWeight,
                'user_id' => $userId,
            ];

            if (in_array($carcassType, ['G1032', 'G1033', 'G1034'])) {
                // Insert sale data for specific carcass types
                DB::table('sales')->insert(array_merge($baseData, ['process_code' => 0]));
                Toastr::success('Sale recorded successfully', 'Success');
                return redirect()->back();
            }

            // Adjust process code and carcass number for beheading data based on carcass type
            $processCode = $carcassType === 'G1031' ? 1 : 0;
            $noOfCarcass = $carcassType === 'G1035' ? (int)$noOfCarcass / 2 : $noOfCarcass;

            // Define complete data for beheading, including any adjustments
            $beheadingData = array_merge($baseData, [
                'no_of_carcass' => $noOfCarcass,
                'process_code' => $processCode,
            ]);

            // Insert beheading data
            DB::table('beheading_data')->insert($beheadingData);

            // Map process_code to process name
            $beheadingData['process_name'] = $helpers->getProcessName($beheadingData['process_code']);

            // Publish the entire beheading data to the queue
            $helpers->publishToQueue($beheadingData, 'production_data_order_beheading.bc');

            Toastr::success('Record inserted successfully', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()->withInput();
        }
    }

    public function saveScaleTwoData(Request $request, Helpers $helpers)
    {
        $user_id = $helpers->authenticatedUserId();

        try {
            $item_code = $request->item_code;

            $data = [
                'item_code' => $item_code,
                'no_of_pieces' => $request->no_of_items,
                'actual_weight' => $request->reading2,
                'net_weight' => $request->net2,
                'user_id' => $user_id,
            ];

            if ($request->for_sale == 'on') {
                if ($request->carcass_type == 'G1031') {
                    $data['item_code'] = $helpers->getSowItemCodeConversion($item_code);
                }
                $data['no_of_carcass'] = 0;
                $data['process_code'] = 0; // process behead pig by default

                DB::table('sales')->insert($data);
                Toastr::success('Sale recorded successfully', 'Success');
                return redirect()->back()->withInput();
            } else {
                $data['carcass_type'] = $request->carcass_type;
                $data['process_code'] = $request->carcass_type == 'G1031' ? '3' : '2';
                $data['product_type'] = $request->product_type;

                if ($request->carcass_type == 'G1031') {
                    $data['item_code'] = $helpers->getSowItemCodeConversion($item_code);
                }

                // Map process_code to process name
                $data['process_name'] = $helpers->getProcessName($data['process_code']);

                // Publish to the queue
                $helpers->publishToQueue($data, 'production_data_order_breaking.bc');

                DB::table('butchery_data')->insert($data);
                Toastr::success('Record inserted successfully', 'Success');
            }
            return redirect()->back()->withInput();

        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()->withInput();
        }
    }

    public function updateScaleOneData(Request $request, Helpers $helpers)
    {
        try {
            // update
            $process_code = 0; //Behead Pig

            if ($request->edit_carcass == 'G1031') {
                $process_code = 1; //Behead sow
            }

            DB::transaction(function () use ($request, $helpers, $process_code) {
                DB::table('beheading_data')
                    ->where('id', $request->item_id1)
                    ->update([
                        'item_code' => $request->edit_carcass,
                        'process_code' => $process_code,
                        'no_of_carcass' => $request->edit_no_carcass,
                        'actual_weight' => $request->edit_weight1,
                        'net_weight' => $request->edit_weight1 - 2.4,
                        'updated_at' => now(),
                    ]);

                $helpers->insertChangeDataLogs('beheading_data', $request->item_id1, '3');
            });

            Toastr::success("record {$request->item_name1} updated successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function updateSalesData(Request $request, Helpers $helpers)
    {
        try {
            // update
            DB::transaction(function () use ($request, $helpers) {
                DB::table('sales')
                    ->where('id', $request->item_id)
                    ->update([
                        'item_code' => $request->edit_carcass,
                        'no_of_pieces' => $request->edit_no_carcass,
                        'actual_weight' => $request->edit_weight,
                        'net_weight' => $request->edit_weight - (2.4 * $request->edit_no_carcass),
                        'updated_at' => Carbon::now(),
                    ]);

                $helpers->insertChangeDataLogs('sales', $request->item_id, '3');
            });

            Toastr::success("record {$request->item_name} updated successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function updateTransfersData(Request $request, Helpers $helpers)
    {
        try {
            // update
            DB::transaction(function () use ($request, $helpers) {
                DB::table('butchery_transfers')
                    ->where('id', $request->item_id)
                    ->update([
                        'item_code' => $request->edit_carcass,
                        'actual_weight' => $request->edit_weight,
                        'net_weight' => $request->edit_weight - (1.8 * $request->edit_crates),
                        'updated_at' => Carbon::now(),
                    ]);

                $helpers->insertChangeDataLogs('butchery_transfers', $request->item_id, '3');
            });

            Toastr::success("record {$request->item_name} updated successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function updateSalesReturns(Request $request, Helpers $helpers)
    {
        try {
            // do sale return 
            DB::transaction(function () use ($request, $helpers) {
                // update returned flag 
                DB::table('sales')
                    ->where('id', $request->return_item_id)
                    ->update([
                        'returned' => 1,
                        'updated_at' => Carbon::now(),
                    ]);

                // insert negative sales
                DB::table('sales')->insert([
                    'item_code' => $request->return_item_code,
                    'no_of_pieces' => -1 * abs($request->return_no_carcass),
                    'no_of_carcass' => 0,
                    'actual_weight' => -1 * abs($request->return_weight),
                    'process_code' => 0, //process behead pig by default
                    'returned' => 2,
                    'net_weight' => -1 * abs($request->return_weight - (2.4 * $request->return_no_carcass)),
                    'user_id' => $helpers->authenticatedUserId(),
                ]);
            });
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }

        Toastr::success("Sales return of {$request->item_name} completed successfully", 'Success');
        return redirect()->back();
    }

    public function updateScaleTwoData(Request $request, Helpers $helpers)
    {
        try {
            $carcass_type = 'G1030';

            if ($request->edit_product == 'G1108' || $request->edit_product == 'G1109' || $request->edit_product == 'G1110') {
                # code...
                $carcass_type = 'G1031';
            }
            // update
            DB::transaction(function () use ($request, $helpers, $carcass_type) {
                DB::table('butchery_data')
                    ->where('id', $request->item_id)
                    ->update([
                        'carcass_type' => $carcass_type,
                        'item_code' => $request->edit_product,
                        'actual_weight' => $request->edit_weight,
                        'no_of_items' => $request->edit_no_pieces,
                        'net_weight' => $request->edit_weight - 7.50,
                        'updated_at' => Carbon::now(),
                    ]);

                $helpers->insertChangeDataLogs('butchery_data', $request->item_id, '3');
            });

            Toastr::success("record {$request->item_name} updated successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function loadSlaughterDataAjax(Request $request)
    {
        $baconers = Cache::remember('baconers_load_ajax', 1, function () {
            $helpers = new Helpers();
            return DB::table('slaughter_data')
                ->whereDate('created_at', $helpers->getButcheryDate())
                ->where('item_code', 'G0110')
                ->count();
        });

        $sows = Cache::remember('sows_load_ajax', 60, function () {
            $helpers = new Helpers();
            return DB::table('slaughter_data')
                ->whereDate('created_at', $helpers->getButcheryDate())
                ->where('item_code', 'G0111')
                ->count();
        });

        $data = array('baconers' => $baconers, 'sows' => $sows);

        return response()->json($data);
    }

    public function scaleThree(Helpers $helpers)
    {
        $title = "Scale-3";

        $filter = Session::get('session_role');

        $configs = Cache::remember('deboning_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('section', 'butchery')
                ->where('scale', 'deboning')
                ->select('scale', 'tareweight', 'comport')
                ->get()->toArray();
        });

        $products = Cache::remember('all_products_scale3', now()->addMinutes(480), function () {
            return DB::table('products')
                ->where('product_processes.process_code', '!=', 15) //excluding marination products
                ->join('product_processes', 'product_processes.product_code', '=', 'products.code')
                ->join('processes', 'product_processes.process_code', '=', 'processes.process_code')
                ->join('product_types', 'product_processes.product_type', '=', 'product_types.code')
                ->select(DB::raw('TRIM(products.code) as code'), 'products.description', 'product_types.description as product_type_name', 'product_types.code as product_type_code', 'product_processes.process_code', 'processes.process', 'processes.shortcode')
                ->get();
        });

        $deboning_data = DB::table('deboned_data')
            ->where('processes.process_code', '!=', '15')
            ->leftJoin('product_types', 'deboned_data.product_type', '=', 'product_types.code')
            ->leftJoin('processes', 'deboned_data.process_code', '=', 'processes.process_code')
            ->leftJoin('products', 'deboned_data.item_code', '=', 'products.code')
            ->select('deboned_data.*', 'product_types.code AS type_id', 'product_types.description AS product_type', 'processes.process', 'processes.process_code', 'products.description')
            ->orderBy('deboned_data.created_at', 'DESC')
            ->when($filter == 'admin', function ($q) {
                $q->whereBetween(
                    'deboned_data.created_at',
                    [now()->startOfWeek(), now()->endOfWeek()]
                ); // today plus last 7 days
            })
            ->when($filter != 'admin', function ($q) {
                $q->whereDate('deboned_data.created_at', today()); // today only
            })
            ->get();

        return view('butchery.scale3', compact('title', 'products', 'configs', 'deboning_data', 'helpers'));
    }

    public function saveScaleThreeData(Request $request, Helpers $helpers)
    {
        try {
            $product_type = match ($request->product_type) {
                "By Product" => 2,
                "Intake" => 3,
                default => 1,
            };

            $prod_date = $request->prod_date === "yesterday" ? today()->subDays(1) : now();
            $item_code = explode('-', $request->product)[1];

            $data = [
                'item_code' => $item_code,
                'actual_weight' => $request->reading,
                'net_weight' => $request->net,
                'process_code' => (int)$request->production_process_code,
                'product_type' => $product_type,
                'no_of_crates' => $request->no_of_crates - 1,
                'no_of_pieces' => $request->no_of_pieces,
                'user_id' => $helpers->authenticatedUserId(),
            ];

            if ($request->for_transfer === 'on') {
                $data['transfer_to'] = $request->transfer_to;
                DB::table('butchery_transfers')->insert($data);
                Toastr::success("Transfer record {$request->product} inserted successfully", 'Success');
                return redirect()->back()->withInput();
            } else {
                $data['narration'] = $request->desc;
                $data['batch_no'] = $request->batch_no;
                $data['created_at'] = $prod_date;

                // Map process_code to process name
                $data['process_name'] = $helpers->getProcessName($data['process_code']);

                // Publish to the queue
                $helpers->publishToQueue($data, 'production_data_order_deboning.bc');

                DB::transaction(fn() => DB::table('deboned_data')->insert($data));
                Toastr::success("Deboning record {$request->product} inserted successfully", 'Success');
            }

            return redirect()->back()->withInput();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()->withInput();
        }
    }

    public function updateScaleThreeData(Request $request, Helpers $helpers)
    {
        try {
            // update
            DB::transaction(function () use ($request, $helpers) {
                DB::table('deboned_data')
                    ->where('id', $request->item_id)
                    ->update([
                        'item_code' => $request->edit_product,
                        'process_code' => $request->edit_production_process,
                        'product_type' => $request->edit_product_type2,
                        'actual_weight' => $request->edit_weight,
                        'net_weight' => $request->edit_weight - (1.8 * $request->edit_crates),
                        'narration' => $request->edit_narration,
                        'no_of_pieces' => $request->edit_no_pieces,
                        'edited' => 1,
                        'updated_at' => Carbon::now(),
                    ]);

                $helpers->insertChangeDataLogs('deboned_data', $request->item_id, '3');
            });

            Toastr::success("record {$request->item_name} updated successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function getProductDetailsAjax(Request $request)
    {
        $data = DB::table('products')
            ->join('product_processes', 'product_processes.product_code', '=', 'products.code')
            ->join('processes', 'product_processes.process_code', '=', 'processes.process_code')
            ->where('products.code', $request->product_code)
            ->where('processes.shortcode', $request->shortcode)
            ->where('product_processes.product_type', $request->product_type_code)
            ->select('product_processes.product_type', 'product_processes.process_code', 'product_processes.product_type', 'process', 'description')
            ->get();

        return response()->json($data);
    }

    public function getProductProcessesAjax(Request $request)
    {
        $processes = DB::table('product_processes')
            ->where('product_code', $request->product_code)
            ->select('process_code')
            ->get();

        return response()->json($processes);
    }

    public function products(Helpers $helpers)
    {
        $title = "products";

        $products = Cache::remember('products_list', now()->addMinutes(120), function () {
            return DB::table('products')
                ->get();
        });

        return view('butchery.products', compact('title', 'products', 'helpers'));
    }

    public function addProductProcess(Request $request, Helpers $helpers)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'process_code' => 'required',
        ]);

        if ($validator->fails()) {
            # failed validation
            $messages = $validator->errors();
            foreach ($messages->all() as $message) {
                Toastr::error($message, 'Error!');
            }
            return back()
                ->withInput()
                ->with('input_errors', 'add_productProcess')
                ->withErrors($validator);
        }

        try {
            DB::transaction(function () use ($request) {
                // delete existing processes of same product process
                DB::table('product_processes')
                    ->where('product_code', explode('-', $request->product)[1] ?? null)
                    ->where('product_type', $request->product_type)
                    ->delete();

                // insert production processes
                foreach ($request->process_code as $code) {

                    DB::table('product_processes')->insert(
                        [
                            'product_code' => explode('-', $request->product)[1] ?? null,
                            'process_code' => $code,
                            'product_type' => $request->product_type,
                        ]
                    );
                }
            });

            $helpers->forgetCache('deboning_list_scale3');

            Toastr::success("Item {$request->product} with production process(es) added successfully", 'Success');
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
        }

        return back();
    }

    public function deleteProductProcess(Request $request, Helpers $helpers)
    {
        try {
            //code...
            DB::table('product_processes')
                ->where('id', $request->item_id)
                ->where('process_code', $request->del_process_code)
                ->delete();

            $helpers->forgetCache('deboning_list_scale3');

            Toastr::success("record {$request->item_name} deleted successfully", 'Success');
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
        }

        return back();
    }

    public function loadProductionProcesses(Request $request)
    {
        $processes = DB::table('processes')
            ->select('process_code', 'process')
            ->get();

        return response()->json($processes);
    }

    public function loadProductionProcessesEdit(Request $request)
    {
        $processes = DB::table('product_processes')
            ->select('process_code')
            ->where('product_code', $request->product_code)
            ->where('product_type', $request->product_type)
            ->get()->toArray();

        return response()->json($processes);
    }

    public function weighSplitting(Helpers $helpers)
    {
        $title = "Splitting-weights";

        $splitted_data = DB::table('splitted_weights')
            ->orderBy('created_at', 'DESC')
            ->get();

        $products = DB::table('products')
            ->get();

        $processes = DB::table('processes')
            ->where('process_code', '>=', 4)
            ->get();

        return view('butchery.split_weights', compact('title', 'splitted_data', 'helpers', 'products', 'processes'));
    }

    public function loadSplitData(Request $request)
    {
        $date = Carbon::parse($request->dateinput);
        $to_split = DB::table('deboned_data')
            ->where('splitted', 0)
            ->whereDate('created_at', $date)
            ->select('deboned_data.item_code', DB::raw('SUM(deboned_data.net_weight) as total_weight'))
            ->groupBy('deboned_data.item_code')
            ->get();

        Session::put('data', $to_split);
        Session::put('display_date', $request->dateinput);
        Session::put('splitting_table', 'show');
        return back();
    }

    public function saveWeighSplitting(Request $request, Helpers $helpers)
    {
        try {
            //inserts
            DB::transaction(function () use ($request, $helpers) {
                //insert 1st row
                DB::table('splitted_weights')->insert([
                    'parent_item' => $helpers->getProductCode($request->item_name),
                    'new_item' => $request->new_item1,
                    'net_weight' => $request->new_weight1,
                    'process_code' => $request->new_process1,
                    'percentage' => $request->percent1,
                ]);

                if ($request->new_item2) {
                    # insert row 2
                    DB::table('splitted_weights')->insert([
                        'parent_item' => $helpers->getProductCode($request->item_name),
                        'new_item' => $request->new_item2,
                        'net_weight' => $request->new_weight2,
                        'process_code' => $request->new_process2,
                        'percentage' => $request->percent2,
                    ]);
                }

                if ($request->new_item3) {
                    # insert row 3
                    DB::table('splitted_weights')->insert([
                        'parent_item' => $helpers->getProductCode($request->item_name),
                        'new_item' => $request->new_item3,
                        'net_weight' => $request->new_weight3,
                        'process_code' => $request->new_process3,
                        'percentage' => $request->percent3,
                    ]);
                }

                //update splitted
                DB::table('deboned_data')
                    ->where('splitted', 0)
                    // ->whereDate('created_at', Carbon::today())
                    ->where('item_code', $helpers->getProductCode($request->item_name))
                    ->update(['splitted' => 1]);
            });

            Session::forget('data');
            Session::forget('display_date');
            Session::forget('splitting_table');

            Toastr::success("record {$request->item_name} splitted successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function addProduct(Request $request, Helpers $helpers)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required',
        ]);

        if ($validator->fails()) {
            # failed validation
            $messages = $validator->errors();
            foreach ($messages->all() as $message) {
                Toastr::error($message, 'Error!');
            }
            return back()
                ->withInput()
                ->with('input_errors', 'add_product')
                ->withErrors($validator);
        }

        try {
            # insert/update record
            DB::transaction(function () use ($request) {

                $product = Product::updateOrCreate(
                    [

                        'code' => strtoupper(str_replace(' ', '', $request->code)),
                    ],
                    [
                        'description' => $request->product,
                        'unit_of_measure' => 'KG',
                        'product_type' => $request->product_type,
                        'updated_at' => now(),
                    ]
                );
            });

            $helpers->forgetCache('products_list');
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput()
                ->with('input_errors', 'add_product');
        }

        Toastr::success("product {$request->product} added/updated successfully", 'Success');
        return redirect()->back();
    }

    public function scaleSettings(Helpers $helpers, $filter, $lay = null)
    {
        $title = "Scale";

        $scale_settings = DB::table('scale_configs')
            ->where('section', $filter)
            ->get();

        $layout = $lay;

        return view($filter . '.scale_settings', compact('title', 'scale_settings', 'helpers', 'layout', 'filter'));
    }

    public function UpdateScalesettings(Request $request, Helpers $helpers)
    {
        // dd($request->all());
        try {
            // forget configs cache
            $helpers->optimizeCache();

            //update
            DB::table('scale_configs')
                ->where('id', $request->item_id)
                ->update([
                    'comport' => $request->edit_comport,
                    'ip_address' => $request->edit_ip_address,
                    'tareweight' => $request->edit_tareweight,
                    'updated_at' => Carbon::now(),
                ]);

            Toastr::success("record {$request->item_name} updated successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function changePassword()
    {
        $title = "password";
        return view('butchery.change_password', compact('title'));
    }

    public function getBeheadingReport(Helpers $helpers)
    {
        $title = "Beheading-Report";
        $beheading_data = DB::table('beheading_data')
            ->leftJoin('products', 'beheading_data.item_code', '=', 'products.code')
            ->leftJoin('processes', 'beheading_data.process_code', '=', 'processes.process_code')
            ->select('beheading_data.*', 'products.description AS product_type', 'processes.process')
            ->orderBy('beheading_data.created_at', 'DESC')
            ->whereDate('beheading_data.created_at', '>=', today()->subDays(7))
            ->get();

        return view('butchery.beheading', compact('title', 'beheading_data', 'helpers'));
    }

    public function combinedBeheadingReport(Request $request)
    {
        $from_date = Carbon::parse($request->from_date);
        $to_date = Carbon::parse($request->to_date);

        $beheading_combined = DB::table('beheading_data')
            ->whereDate('beheading_data.created_at', '>=', $from_date)
            ->whereDate('beheading_data.created_at', '<=', $to_date)
            ->leftJoin('products', 'beheading_data.item_code', '=', 'products.code')
            ->select('beheading_data.item_code', 'products.description AS Carcass', DB::raw('SUM(beheading_data.no_of_carcass) as total_carcasses'), DB::raw('SUM(beheading_data.net_weight) as total_net'))
            ->groupBy('beheading_data.item_code', 'products.description')
            ->get();

        $exports = Session::put('session_export_data', $beheading_combined);

        return Excel::download(new BeheadedCombinedExport, 'BeheadingPigSummaryReportFor-' . $request->from_date . ' to ' . $request->to_date . '.xlsx');
    }

    public function getBrakingReport(Helpers $helpers)
    {
        $title = "Breaking-Report";
        $butchery_data = DB::table('butchery_data')
            ->leftJoin('products', 'butchery_data.item_code', '=', 'products.code')
            ->leftJoin('processes', 'butchery_data.process_code', '=', 'processes.process_code')
            ->select('butchery_data.*', 'products.description AS product_type', 'processes.process')
            ->orderBy('butchery_data.created_at', 'DESC')
            ->whereDate('butchery_data.created_at', '>=', today()->subDays(7))
            ->get();

        return view('butchery.breaking', compact('title', 'butchery_data', 'helpers'));
    }

    public function combinedBreakingReport(Request $request)
    {
        $from_date = Carbon::parse($request->from_date);
        $to_date = Carbon::parse($request->to_date);

        $butchery_combined = DB::table('butchery_data')
            ->whereDate('butchery_data.created_at', '>=', $from_date)
            ->whereDate('butchery_data.created_at', '<=', $to_date)
            ->leftJoin('products', 'butchery_data.item_code', '=', 'products.code')
            ->select('butchery_data.item_code', 'products.description AS product_type', DB::raw('SUM(butchery_data.no_of_items) as total_pieces'), DB::raw('SUM(butchery_data.net_weight)'))
            ->groupBy('butchery_data.item_code', 'products.description')
            ->get();

        $exports = Session::put('session_export_data', $butchery_combined);

        return Excel::download(new BreakingCombinedExport, 'BreakingPigSummaryReportFor-' . $request->from_date . ' to ' . $request->to_date . '.xlsx');
    }

    public function getDeboningReport(Helpers $helpers)
    {
        $title = "Deboning-Report";

        $deboning_data = DB::table('deboned_data')
            ->leftJoin('product_types', 'deboned_data.product_type', '=', 'product_types.code')
            ->leftJoin('processes', 'deboned_data.process_code', '=', 'processes.process_code')
            ->leftJoin('products', 'deboned_data.item_code', '=', 'products.code')
            ->select('deboned_data.*', 'product_types.description AS product_type', 'processes.process', 'products.description')
            ->orderBy('deboned_data.created_at', 'DESC')
            ->whereDate('deboned_data.created_at', '>=', today()->subDays(7))
            ->get();

        return view('butchery.deboned', compact('title', 'deboning_data', 'helpers'));
    }

    public function combinedDeboningReport(Request $request)
    {
        $from_date = Carbon::parse($request->from_date);
        $to_date = Carbon::parse($request->to_date);

        $deboned_combined = DB::table('deboned_data')
            ->whereDate('deboned_data.created_at', '>=', $from_date)
            ->whereDate('deboned_data.created_at', '<=', $to_date)
            ->leftJoin('products', 'deboned_data.item_code', '=', 'products.code')
            ->leftJoin('processes', 'deboned_data.process_code', '=', 'processes.process_code')
            ->leftJoin('product_types', 'deboned_data.product_type', '=', 'product_types.code')
            ->select('deboned_data.item_code', 'products.description AS product', 'product_types.description', 'processes.process', DB::raw('SUM(deboned_data.no_of_crates) AS no_of_crates'), DB::raw('SUM(deboned_data.no_of_pieces) AS no_of_pieces'), DB::raw('SUM(deboned_data.net_weight) AS net_weight'))
            ->groupBy('deboned_data.item_code', 'products.description', 'product_types.description', 'processes.process')
            ->get();

        $exports = Session::put('session_export_data', $deboned_combined);

        return Excel::download(new DebonedCombinedExport, 'DebonedPigSummaryReport-' . $request->from_date . ' to ' . $request->to_date . '.xlsx');
    }

    public function getSalesReport(Helpers $helpers)
    {
        $title = "Sales-Report";

        $day_filter = 12;

        $sales_data = DB::table('sales')
            ->leftJoin('products', 'sales.item_code', '=', 'products.code')
            ->select('sales.*', 'products.description')
            ->orderBy('created_at', 'DESC')
            ->whereDate('sales.created_at', '>=', today()->subDays((int)$day_filter))
            ->get();

        return view('butchery.sales', compact('title', 'sales_data', 'helpers', 'day_filter'));
    }

    public function getTransfersReport(Helpers $helpers)
    {
        $title = "Transfers-Report";

        $products_list = DB::table('products')
            ->select('id', 'code', 'description')
            ->get();

        $transfers_data = DB::table('butchery_transfers')
            ->leftJoin('products', 'butchery_transfers.item_code', '=', 'products.code')
            ->select('butchery_transfers.*', 'products.description')
            ->orderBy('created_at', 'DESC')
            ->whereDate('butchery_transfers.created_at', '>=', today()->subDays(7))
            ->get();

        return view('butchery.transfers', compact('title', 'transfers_data', 'products_list', 'helpers'));
    }

    public function getDeboningProductsList()
    {
        $title = "Deboning-Products-List";

        $processes = Cache::remember('processes_list', now()->addHours(8), function () {
            return DB::table('processes')->get();
        });

        $products_list = DB::table('products')
            ->select('id', 'code', 'description')
            ->get();

        $products = Cache::remember('deboning_list_scale3', now()->addMinutes(120), function () {
            return DB::table('products')
                ->join('product_processes', 'product_processes.product_code', '=', 'products.code')
                ->join('processes', 'product_processes.process_code', '=', 'processes.process_code')
                ->select('product_processes.id', 'products.id as product_id', DB::raw('TRIM(products.code) as code'), 'products.description', 'product_processes.product_type', 'product_processes.process_code', 'processes.process', 'processes.shortcode')
                ->orderBy('products.code')
                ->get();
        });

        return view('butchery.products_list_deboning', compact('title', 'products', 'products_list', 'processes'));
    }

    public function weighMarination(Helpers $helpers)
    {
        $title = "Scale-4";

        $configs = Cache::remember('marination_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('section', 'butchery')
                ->where('scale', 'marination')
                ->select('scale', 'tareweight', 'comport')
                ->get()->toArray();
        });

        $products = Cache::remember('marination_products', now()->addMinutes(120), function () {
            return  DB::table('products')
                ->where('processes.process_code', '15')
                ->join('product_processes', 'product_processes.product_code', '=', 'products.code')
                ->join('processes', 'product_processes.process_code', '=', 'processes.process_code')
                ->join('product_types', 'product_processes.product_type', '=', 'product_types.code')
                ->select(DB::raw('TRIM(products.code) as code'), 'products.description', 'product_types.description as product_type_name', 'product_processes.process_code', 'product_processes.product_type as product_type_code', 'processes.process', 'processes.shortcode')
                ->get();
        });

        $marination_data = DB::table('deboned_data')
            ->where('processes.process_code', '15')
            ->whereDate('deboned_data.created_at', '>=', today()->subDays(7))
            ->leftJoin('product_types', 'deboned_data.product_type', '=', 'product_types.code')
            ->leftJoin('processes', 'deboned_data.process_code', '=', 'processes.process_code')
            ->leftJoin('products', 'deboned_data.item_code', '=', 'products.code')
            ->select('deboned_data.*', 'product_types.code AS type_id', 'product_types.description AS product_type', 'processes.process', 'processes.process_code', 'products.description')
            ->orderBy('deboned_data.created_at', 'DESC')
            ->get();

        return view('butchery.marination', compact('title', 'products', 'configs', 'marination_data', 'helpers'));
    }

    public function saveMarinationData(Request $request, Helpers $helpers)
    {
        $item = explode("-", $request->product);

        try {
            //saving...
            DB::table('deboned_data')->insert([
                'item_code' => $item[0],
                'actual_weight' => $request->reading,
                'net_weight' => $request->net,
                'process_code' => '15',
                'product_type' => $item[1],
                'no_of_pieces' => '0',
                'no_of_crates' => $request->no_of_crates,
                'user_id' => $helpers->authenticatedUserId(),
                'created_at' => Carbon::parse($request->marination_date),

            ]);
            Toastr::success("Item {$item[0]} recorded successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function updateMarinationData(Request $request, Helpers $helpers)
    {
        try {
            // update
            DB::transaction(function () use ($request, $helpers) {
                DB::table('deboned_data')
                    ->where('id', $request->item_id)
                    ->update([
                        'item_code' => $request->edit_product,
                        'actual_weight' => $request->edit_weight,
                        'net_weight' => $request->edit_weight - (1.8 * $request->edit_crates),
                        'updated_at' => now(),
                    ]);

                $helpers->insertChangeDataLogs('deboned_data', $request->item_id, '3');
            });

            Toastr::success("record {$request->item_name} updated successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function insertItemLocations(Helpers $helpers)
    {
        return $helpers->insertItemLocations();
    }
}
