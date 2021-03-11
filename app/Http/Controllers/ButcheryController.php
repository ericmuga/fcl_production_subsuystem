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

        $lined_baconers = Cache::remember('lined_baconers', now()->addMinutes(120), function () {
            $helpers = new Helpers();
            return DB::table('slaughter_data')
                ->where('item_code', 'G0110')
                ->whereDate('created_at', $helpers->getButcheryDate())
                ->count();
        });

        $lined_sows = Cache::remember('lined_sows', now()->addMinutes(120), function () {
            $helpers = new Helpers();
            return DB::table('slaughter_data')
                ->where('item_code', 'G0111')
                ->whereDate('created_at', $helpers->getButcheryDate())
                ->count();
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
            ->where('item_code', 'G1100')
            ->sum('net_weight');

        $s_shoulders = DB::table('butchery_data')
            ->where('carcass_type', 'G1031')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1101')
            ->sum('net_weight');

        $s_middles = DB::table('butchery_data')
            ->where('carcass_type', 'G1031')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1102')
            ->sum('net_weight');

        return view('butchery.dashboard', compact('title', 'baconers', 'sows', 'baconers_weight', 'sows_weight', 'lined_baconers', 'lined_sows', 'three_parts_baconers', 'three_parts_sows', 'helpers', 'b_legs', 'b_shoulders', 'b_middles', 's_legs', 's_shoulders', 's_middles'));
    }

    public function scaleOneAndTwo(Helpers $helpers)
    {
        $title = "Scale-1&2";

        $configs = DB::table('scale_configs')
            ->where('section', 'butchery')
            ->select('scale', 'tareweight', 'comport')
            ->get()->toArray();

        $products = Cache::remember('products_scale12', now()->addMinutes(120), function () {
            return DB::table('products')
                ->orWhere('code', 'G1100')
                ->orWhere('code', 'G1101')
                ->orWhere('code', 'G1102')
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
            if ($request->carcass_type == "G1032" || $request->carcass_type == "G1033") {

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

            DB::table('beheading_data')->insert([
                'item_code' => $request->carcass_type,
                'no_of_carcass' => $request->no_of_carcass,
                'actual_weight' => $request->reading,
                'net_weight' => $request->net,
                'process_code' => $process_code,
                'user_id' => $helpers->authenticatedUserId(),
            ]);

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
        try {
            # insert record
            $process_code = 2; //Breaking Pig, (Leg, Mdl, Shld)
            if ($request->carcass_type == 'G1031') {
                $process_code = 3; //Breaking Sow into Leg,Mid,&Shd
            }

            DB::table('butchery_data')->insert([
                'carcass_type' =>  $request->carcass_type,
                'item_code' =>  $request->item_code,
                'actual_weight' => $request->reading2,
                'net_weight' => $request->net2,
                'no_of_items' => $request->no_of_items,
                'process_code' => $process_code,
                'product_type' => $request->product_type,
                'user_id' => $helpers->authenticatedUserId(),
            ]);

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

    public function updateScaleOneData(Request $request)
    {
        try {
            // update
            DB::table('beheading_data')
                ->where('id', $request->item_id1)
                ->update([
                    'item_code' => $request->edit_carcass,
                    'no_of_carcass' => $request->edit_no_carcass,
                    'actual_weight' => $request->edit_weight1,
                    'net_weight' => $request->edit_weight1 - 2.4,
                    'updated_at' => Carbon::now(),
                ]);


            Toastr::success("record {$request->item_name1} updated successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function updateSalesData(Request $request)
    {
        try {
            // update
            DB::table('sales')
                ->where('id', $request->item_id)
                ->update([
                    'item_code' => $request->edit_carcass,
                    'no_of_carcass' => $request->edit_no_carcass,
                    'actual_weight' => $request->edit_weight,
                    'net_weight' => $request->edit_weight - (2.4 * $request->edit_no_carcass),
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

    public function updateScaleTwoData(Request $request)
    {
        try {
            // update
            DB::table('butchery_data')
                ->where('id', $request->item_id)
                ->update([
                    'item_code' => $request->edit_product,
                    'actual_weight' => $request->edit_weight,
                    'net_weight' => $request->edit_weight - 7.50,
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

        $configs = DB::table('scale_configs')
            ->where('section', 'butchery')
            ->where('scale', 'scale 3')
            ->select('scale', 'tareweight', 'comport')
            ->get()->toArray();

        $products = Cache::remember('products_scale3', now()->addMinutes(120), function () {
            return DB::table('products')
                ->where('id', '>', 6)
                ->select('code', 'description')
                ->get();
        });

        $deboning_data = DB::table('deboned_data')
            ->whereDate('deboned_data.created_at', Carbon::today())
            ->leftJoin('product_types', 'deboned_data.product_type', '=', 'product_types.code')
            ->leftJoin('processes', 'deboned_data.process_code', '=', 'processes.process_code')
            ->select('deboned_data.*', 'product_types.description AS product_type', 'processes.process')
            ->orderBy('deboned_data.created_at', 'DESC')
            ->get();

        return view('butchery.scale3', compact('title', 'products', 'configs', 'deboning_data', 'helpers'));
    }

    public function saveScaleThreeData(Request $request, Helpers $helpers)
    {
        try {
            $product_type = 1;
            if ($request->product_type == "By Product") {
                $product_type = 2;
            }

            # insert record
            DB::table('deboned_data')->insert([
                'item_code' =>  $request->product,
                'actual_weight' => $request->reading,
                'net_weight' => $request->net,
                'process_code' => (int)$request->production_process,
                'product_type' => $product_type,
                'no_of_pieces' => $request->no_of_pieces,
                'user_id' => $helpers->authenticatedUserId(),
            ]);

            Toastr::success("record {$request->product} inserted successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function updateScaleThreeData(Request $request)
    {
        try {
            // update
            DB::table('deboned_data')
                ->where('id', $request->item_id)
                ->update([
                    'actual_weight' => $request->edit_weight,
                    'net_weight' => $request->edit_weight - (1.8 * $request->edit_crates),
                    'no_of_pieces' => $request->edit_no_pieces,
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

    public function getProductTypeAjax(Request $request)
    {
        $data = DB::table('products')
            ->where('code', $request->product_code)
            ->select('product_type')
            ->first();
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
                ->where('code', '!=', '')
                ->get();
        });

        return view('butchery.products', compact('title', 'products', 'helpers'));
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
            'code' => 'required|unique:products,code',
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

        $product = Product::create([
            'code' => $request->code,
            'description' => $request->product,
            'product_type' => $request->product_type,
            'input_type' => $request->input_type,
            'often' => $request->often,
            'user_id' => $helpers->authenticatedUserId(),

        ]);

        Toastr::success("product {$request->product} inserted successfully", 'Success');
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

    public function UpdateScalesettings(Request $request)
    {
        try {
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
            ->get();

        return view('butchery.beheading', compact('title', 'beheading_data', 'helpers'));
    }

    public function combinedBeheadingReport(Request $request)
    {
        $beheading_combined = DB::table('beheading_data')
            ->whereDate('beheading_data.created_at', Carbon::parse($request->date))
            ->leftJoin('products', 'beheading_data.item_code', '=', 'products.code')
            ->select('beheading_data.item_code', 'products.description AS Carcass', DB::raw('SUM(beheading_data.no_of_carcass) as total_carcasses'), DB::raw('SUM(beheading_data.net_weight) as total_net'))
            ->groupBy('beheading_data.item_code', 'products.description')
            ->get();

        $exports = Session::put('session_export_data', $beheading_combined);

        return Excel::download(new BeheadedCombinedExport, 'BeheadingPigSummaryReport-' . $request->date . '.xlsx');
    }

    public function getBrakingReport(Helpers $helpers)
    {
        $title = "Braking-Report";
        $butchery_data = DB::table('butchery_data')
            ->leftJoin('products', 'butchery_data.item_code', '=', 'products.code')
            ->leftJoin('processes', 'butchery_data.process_code', '=', 'processes.process_code')
            ->select('butchery_data.*', 'products.description AS product_type', 'processes.process')
            ->get();

        return view('butchery.breaking', compact('title', 'butchery_data', 'helpers'));
    }

    public function combinedBreakingReport(Request $request)
    {
        $butchery_combined = DB::table('butchery_data')
            ->whereDate('butchery_data.created_at', Carbon::parse($request->date))
            ->leftJoin('products', 'butchery_data.item_code', '=', 'products.code')
            ->select('butchery_data.item_code', 'products.description AS product_type', DB::raw('SUM(butchery_data.net_weight)'))
            ->groupBy('butchery_data.item_code', 'products.description')
            ->get();

        $exports = Session::put('session_export_data', $butchery_combined);

        return Excel::download(new BreakingCombinedExport, 'BreakingPigSummaryReport-' . $request->date . '.xlsx');
    }

    public function getDeboningReport(Helpers $helpers)
    {
        $title = "Deboning-Report";
        $deboning_data = DB::table('deboned_data')
            ->leftJoin('product_types', 'deboned_data.product_type', '=', 'product_types.code')
            ->leftJoin('processes', 'deboned_data.process_code', '=', 'processes.process_code')
            ->select('deboned_data.*', 'product_types.description AS product_type', 'processes.process')
            ->get();

        return view('butchery.deboned', compact('title', 'deboning_data', 'helpers'));
    }

    public function combinedDeboningReport(Request $request)
    {
        $deboned_combined = DB::table('deboned_data')
            ->whereDate('deboned_data.created_at', Carbon::parse($request->date))
            ->leftJoin('products', 'deboned_data.item_code', '=', 'products.code')
            ->select('deboned_data.item_code', 'products.description AS product', DB::raw('SUM(deboned_data.net_weight)'))
            ->groupBy('deboned_data.item_code', 'products.description')
            ->get();

        $exports = Session::put('session_export_data', $deboned_combined);

        return Excel::download(new DebonedCombinedExport, 'DebonedPigSummaryReport-' . $request->date . '.xlsx');
    }

    public function getSalesReport(Helpers $helpers)
    {
        $title = "Sales-Report";
        $sales_data = DB::table('sales')
            ->leftJoin('products', 'sales.item_code', '=', 'products.code')
            ->select('sales.*', 'products.description')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('butchery.sales', compact('title', 'sales_data', 'helpers'));
    }
}
