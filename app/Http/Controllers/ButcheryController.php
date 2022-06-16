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
use PhpParser\Node\Stmt\TryCatch;

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

        $lined_baconers = Cache::remember('lined_baconers', now()->addMinutes(360), function () {
            $helpers = new Helpers();

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

        $lined_sows = Cache::remember('linedup_sows', now()->addMinutes(360), function () {
            $helpers = new Helpers();

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

        $slaughtered_baconers_weight = Cache::remember('slaughtered_baconers_weight', now()->addMinutes(360), function () {
            $helpers = new Helpers();

            return DB::table('slaughter_data')
                ->whereDate('created_at', $helpers->getButcheryDate())
                ->where('item_code', 'G0110')
                ->sum('net_weight');
        });

        $slaughtered_sows_weight = Cache::remember('slaughtered_sows_weight', now()->addMinutes(360), function () {
            $helpers = new Helpers();

            return DB::table('slaughter_data')
                ->whereDate('created_at', $helpers->getButcheryDate())
                ->where('item_code', 'G0111')
                ->sum('net_weight');
        });

        return view('butchery.dashboard', compact('title', 'baconers', 'sows', 'baconers_weight', 'sows_weight', 'lined_baconers', 'lined_sows', 'three_parts_baconers', 'three_parts_sows', 'helpers', 'b_legs', 'b_shoulders', 'b_middles', 's_legs', 's_shoulders', 's_middles', 'sales', 'slaughtered_baconers_weight', 'slaughtered_sows_weight'));
    }

    public function scaleOneAndTwo(Helpers $helpers)
    {
        $title = "Scale-1&2";

        $configs = Cache::remember('scale12_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('section', 'butchery')
                ->select('scale', 'tareweight', 'comport')
                ->get()->toArray();
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
            // insert sales substr($string, 0, -1);
            if ($request->carcass_type == "G1032" || $request->carcass_type == "G1033" || $request->carcass_type == "G1034") {

                DB::table('sales')->insert([
                    'item_code' => $request->carcass_type,
                    'no_of_carcass' => $request->no_of_carcass,
                    'actual_weight' => $request->reading,
                    'net_weight' => $request->net,
                    'process_code' => 0, //process behead pig by default
                    'user_id' => $helpers->authenticatedUserId(),
                ]);

                Toastr::success('sale recorded successfully', 'Success');
                return redirect()->back();
            }
            // insert beheading data
            $process_code = 0; //Behead Pig
            if ($request->carcass_type == 'G1031') {
                $process_code = 1; //Behead sow
            }

            if ($request->carcass_type == 'G1035') {
                DB::table('beheading_data')->insert([
                    'item_code' => $request->carcass_type,
                    'no_of_carcass' => $request->no_of_carcass,
                    'actual_weight' => $request->reading,
                    'net_weight' => $request->net,
                    'process_code' => '1',
                    'user_id' => $helpers->authenticatedUserId(),
                ]);
            } else {
                DB::table('beheading_data')->insert([
                    'item_code' => $request->carcass_type,
                    'no_of_carcass' => $request->no_of_carcass,
                    'actual_weight' => $request->reading,
                    'net_weight' => $request->net,
                    'process_code' => $process_code,
                    'user_id' => $helpers->authenticatedUserId(),
                ]);
            }

            Toastr::success('record inserted successfully', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function saveScaleTwoData(Request $request, Helpers $helpers)
    {
        $user_id = $helpers->authenticatedUserId();

        try {
            if ($request->for_sale == 'on') {
                # save for sale
                $item_code = $request->item_code;

                if ($request->carcass_type == 'G1031') {
                    # insert sow
                    $item_code = $helpers->getSowItemCodeConversion($request->item_code);
                }

                DB::table('sales')->insert([
                    'item_code' => $item_code,
                    'no_of_carcass' => $helpers->numberOfSalesCarcassesCalculation(
                        $request->no_of_items
                    ),
                    'actual_weight' => $request->reading2,
                    'net_weight' => $request->net2,
                    'process_code' => 0, //process behead pig by default
                    'user_id' => $user_id,
                ]);

                Toastr::success('sale recorded successfully', 'Success');
                return redirect()->back();
            } else {
                # save for production
                if ($request->carcass_type == 'G1031') {
                    # insert sow
                    $item_code = $helpers->getSowItemCodeConversion($request->item_code);

                    DB::table('butchery_data')->insert([
                        'carcass_type' =>  $request->carcass_type,
                        'item_code' => $item_code,
                        'actual_weight' => $request->reading2,
                        'net_weight' => $request->net2,
                        'no_of_items' => $request->no_of_items,
                        'process_code' => '3',
                        'product_type' => $request->product_type,
                        'user_id' => $user_id,
                    ]);
                } else {
                    #insert for baconer
                    DB::table('butchery_data')->insert([
                        'carcass_type' =>  $request->carcass_type,
                        'item_code' =>  $request->item_code,
                        'actual_weight' => $request->reading2,
                        'net_weight' => $request->net2,
                        'no_of_items' => $request->no_of_items,
                        'process_code' => '2',
                        'product_type' => $request->product_type,
                        'user_id' => $user_id,
                    ]);
                }
            }

            Toastr::success('record inserted successfully', 'Success');
            return redirect()
                ->back()
                ->withInput();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function updateScaleOneData(Request $request, Helpers $helpers)
    {
        try {
            // update
            DB::transaction(function () use ($request, $helpers) {
                DB::table('beheading_data')
                    ->where('id', $request->item_id1)
                    ->update([
                        'item_code' => $request->edit_carcass,
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
                        'no_of_carcass' => $request->edit_no_carcass,
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
                DB::table('transfers')
                    ->where('id', $request->item_id)
                    ->update([
                        'item_code' => $request->edit_carcass,
                        'actual_weight' => $request->edit_weight,
                        'net_weight' => $request->edit_weight - (1.8 * $request->edit_crates),
                        'updated_at' => Carbon::now(),
                    ]);

                $helpers->insertChangeDataLogs('transfers', $request->item_id, '3');
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
                    'no_of_carcass' => -1 * abs($request->return_no_carcass),
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

        $configs = Cache::remember('scale3_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('section', 'butchery')
                ->where('scale', 'scale 3')
                ->select('scale', 'tareweight', 'comport')
                ->get()->toArray();
        });

        $products = Cache::remember('all_products_scale3', now()->addMinutes(480), function () {
            return DB::table('products')
                ->where('product_processes.process_code', '!=', 15) //excluding marination products
                ->join('product_processes', 'product_processes.product_id', '=', 'products.id')
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

            $product_type = 1;
            if ($request->product_type == "By Product") {
                $product_type = 2;
            } elseif ($request->product_type == "Intake") {
                $product_type = 3;
            }

            $prod_date = now();
            if ($request->prod_date == "yesterday") {
                $prod_date = now()->subDays(1);
            }

            $item = explode('-', $request->product);
            $item_code = $item[1];

            if ($request->for_transfer == 'on') {
                # transfers
                DB::table('transfers')->insert([
                    'item_code' => $item_code,
                    'actual_weight' => $request->reading,
                    'net_weight' => $request->net,
                    'process_code' => (int)$request->production_process_code,
                    'product_type' => $product_type,
                    'no_of_crates' => $request->no_of_crates - 1,
                    'no_of_pieces' => $request->no_of_pieces,
                    'transfer_to' => $request->transfer_to,
                    'user_id' => $helpers->authenticatedUserId(),
                ]);

                Toastr::success("Transfer record {$request->product} inserted successfully", 'Success');
                return redirect()
                    ->back()
                    ->withInput();
            } else {
                DB::transaction(function () use ($request, $helpers, $item_code, $product_type, $prod_date) {
                    # insert record
                    DB::table('deboned_data')->insert([
                        'item_code' => $item_code,
                        'actual_weight' => $request->reading,
                        'net_weight' => $request->net,
                        'process_code' => (int)$request->production_process_code,
                        'product_type' => $product_type,
                        'no_of_crates' => $request->no_of_crates - 1,
                        'no_of_pieces' => $request->no_of_pieces,
                        'user_id' => $helpers->authenticatedUserId(),
                        'created_at' => $prod_date,
                    ]);
                });

                Toastr::success("Deboning record {$request->product} inserted successfully", 'Success');
                return redirect()
                    ->back()
                    ->withInput();
            }
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
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
                        'no_of_pieces' => $request->edit_no_pieces,
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
            ->join('product_processes', 'product_processes.product_id', '=', 'products.id')
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
        $product_id = DB::table('products')->where('code', $request->product_code)->value('id');

        $processes = DB::table('product_processes')
            ->where('product_id', $product_id)
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
                // delete existing processes of same product
                DB::table('product_processes')
                    ->where('product_id', strtok($request->product,  '-'))
                    ->where('product_type', $request->product_type)
                    ->delete();

                // insert production processes
                foreach ($request->process_code as $code) {

                    DB::table('product_processes')->insert(
                        [
                            'product_id' => strtok($request->product,  '-'),
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
            ->where('product_id', $request->product_id)
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

    public function scaleSettings(Helpers $helpers)
    {
        $title = "Scale";

        $scale_settings = DB::table('scale_configs')
            ->where('section', 'butchery')
            ->get();

        return view('butchery.scale_settings', compact('title', 'scale_settings', 'helpers'));
    }

    public function UpdateScalesettings(Request $request, Helpers $helpers)
    {
        try {
            // forget configs cache
            $helpers->forgetCache('scale12_configs');
            $helpers->forgetCache('scale3_configs');

            //update
            DB::table('scale_configs')
                ->where('id', $request->item_id)
                ->update([
                    'comport' => $request->edit_comport,
                    'baudrate' => $request->edit_baud,
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
            ->where('beheading_data.created_at', '>=', today()->subDays(7))
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
            ->where('butchery_data.created_at', '>=', today()->subDays(7))
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
            ->where('deboned_data.created_at', '>=', today()->subDays(7))
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
        $sales_data = DB::table('sales')
            ->leftJoin('products', 'sales.item_code', '=', 'products.code')
            ->select('sales.*', 'products.description')
            ->orderBy('created_at', 'DESC')
            ->where('sales.created_at', '>=', today()->subDays(7))
            ->get();

        return view('butchery.sales', compact('title', 'sales_data', 'helpers'));
    }

    public function getTransfersReport(Helpers $helpers)
    {
        $title = "Transfers-Report";

        $products_list = DB::table('products')
            ->select('id', 'code', 'description')
            ->get();

        $transfers_data = DB::table('transfers')
            ->leftJoin('products', 'transfers.item_code', '=', 'products.code')
            ->select('transfers.*', 'products.description')
            ->orderBy('created_at', 'DESC')
            ->where('transfers.created_at', '>=', today()->subDays(7))
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
                ->join('product_processes', 'product_processes.product_id', '=', 'products.id')
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

        $configs = Cache::remember('scale3_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('section', 'butchery')
                ->where('scale', 'scale 3')
                ->select('scale', 'tareweight', 'comport')
                ->get()->toArray();
        });

        $products = Cache::remember('marination_products', now()->addMinutes(120), function () {
            return  DB::table('products')
                ->where('processes.process_code', '15')
                ->join('product_processes', 'product_processes.product_id', '=', 'products.id')
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
}
