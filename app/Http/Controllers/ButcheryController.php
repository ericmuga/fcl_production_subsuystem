<?php

namespace App\Http\Controllers;

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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use PhpParser\Node\Stmt\TryCatch;

class ButcheryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Helpers $helpers)
    {
        $title = "dashboard";

        $baconers = BeheadingData::whereDate('created_at', Carbon::today())
            ->where('item_code', "G1030")
            ->sum('no_of_carcass');

        $sows = BeheadingData::whereDate('created_at', Carbon::today())
            ->where('item_code', "G1031")
            ->sum('no_of_carcass');

        $baconers_weight = BeheadingData::whereDate('created_at', Carbon::today())
            ->where('item_code', "G1030")
            ->sum('net_weight');

        $sows_weight = BeheadingData::whereDate('created_at', Carbon::today())
            ->where('item_code', "G1031")
            ->sum('net_weight');

        $butchery_date = $helpers->getButcheryDate();

        $lined_baconers = SlaughterData::where('item_code', 'G0110')
            ->whereDate('created_at', $butchery_date)
            ->count();

        $lined_sows = SlaughterData::where('item_code', 'G0111')
            ->whereDate('created_at', $butchery_date)
            ->count();

        $three_parts_baconers = ButcheryData::where('carcass_type', 'G1030')
            ->whereDate('created_at', Carbon::today())
            ->sum('net_weight');

        $three_parts_sows = ButcheryData::where('carcass_type', 'G1031')
            ->whereDate('created_at', Carbon::today())
            ->sum('net_weight');

        $b_legs = ButcheryData::where('carcass_type', 'G1030')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1100')
            ->sum('net_weight');

        $b_shoulders = ButcheryData::where('carcass_type', 'G1030')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1101')
            ->sum('net_weight');

        $b_middles = ButcheryData::where('carcass_type', 'G1030')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1102')
            ->sum('net_weight');

        $s_legs = ButcheryData::where('carcass_type', 'G1031')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1100')
            ->sum('net_weight');

        $s_shoulders = ButcheryData::where('carcass_type', 'G1031')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1101')
            ->sum('net_weight');

        $s_middles = ButcheryData::where('carcass_type', 'G1031')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1102')
            ->sum('net_weight');

        return view('butchery.dashboard', compact('title', 'baconers', 'sows', 'baconers_weight', 'sows_weight', 'lined_baconers', 'lined_sows', 'three_parts_baconers', 'three_parts_sows', 'butchery_date', 'helpers', 'b_legs', 'b_shoulders', 'b_middles', 's_legs', 's_shoulders', 's_middles'));
    }

    public function scaleOneAndTwo(Helpers $helpers)
    {
        $title = "Scale-1&2";

        $configs = DB::table('scale_configs')
            ->where('section', 'butchery')
            ->select('scale', 'tareweight', 'comport')
            ->get()->toArray();

        $products = DB::table('products')
            ->orWhere('code', 'G1100')
            ->orWhere('code', 'G1101')
            ->orWhere('code', 'G1102')
            ->orderBy('code', 'ASC')
            ->get();

        $beheading_data = DB::table('beheading_data')
            ->whereDate('beheading_data.created_at', Carbon::today())
            ->leftJoin('products', 'beheading_data.item_code', '=', 'products.code')
            ->select('beheading_data.*', 'products.description')
            ->get();

        $butchery_data = DB::table('butchery_data')
            ->whereDate('butchery_data.created_at', Carbon::today())
            ->leftJoin('products', 'butchery_data.item_code', '=', 'products.code')
            ->select('butchery_data.*', 'products.description')
            ->get();

        $inputData = $helpers->getInputData();
        $outputData = $helpers->getOutputData();

        return view('butchery.scale1-2', compact('title', 'configs', 'products', 'beheading_data', 'butchery_data', 'helpers', 'inputData', 'outputData'));
    }

    public function readScaleApiService(Request $request, Helpers $helpers)
    {
        $result = $helpers->get_scale_read($request->comport);
        return response()->json($result);
    }

    public function saveScaleOneData(Request $request)
    {
        try {
            // insert sales substr($string, 0, -1);
            if ($request->carcass_type == "G1032" || $request->carcass_type == "G1033") {
                $new = Sale::create([
                    'item_code' => $request->carcass_type,
                    'no_of_carcass' => $request->no_of_carcass,
                    'actual_weight' => $request->reading,
                    'net_weight' => $request->net,
                    'process_code' => 0, //process behead pig by default
                    'user_id' => Auth::id(),
                ]);

                Toastr::success('sale recorded successfully','Success');
                return redirect()->back();
            }
            // insert beheading data
            $process_code = 0; //Behead Pig
            if ($request->carcass_type == 'G1031') {
                $process_code = 1; //Behead sow
            }

            $new = BeheadingData::create([
                'item_code' => $request->carcass_type,
                'no_of_carcass' => $request->no_of_carcass,
                'actual_weight' => $request->reading,
                'net_weight' => $request->net,
                'process_code' => $process_code,
                'user_id' => Auth::id(),
            ]);

            Toastr::success('record inserted successfully','Success');
            return redirect()->back();

        } catch (\Exception $e) {
            Toastr::error($e->getMessage(),'Error!');
            return back()
                ->withInput();
        }

    }

    public function saveScaleTwoData(Request $request)
    {
        try {
            # insert record
            $process_code = 2; //Breaking Pig, (Leg, Mdl, Shld)
            if ($request->carcass_type == 'G1031') {
                $process_code = 3; //Breaking Sow into Leg,Mid,&Shd

            }
            $new = ButcheryData::create([
                'carcass_type' =>  $request->carcass_type,
                'item_code' =>  $request->item_code,
                'actual_weight' => $request->reading2,
                'net_weight' => $request->net2,
                'no_of_items' => $request->no_of_items,
                'process_code' => $process_code,
                'product_type' => $request->product_type,
                'user_id' => Auth::id(),
            ]);

            Toastr::success('record inserted successfully','Success');
            return redirect()->back();

        } catch (\Exception $e) {
            Toastr::error($e->getMessage(),'Error!');
            return back()
                ->withInput();
        }

    }

    public function updateScaleTwoData(Request $request)
    {
        try {
            //update
            DB::table('butchery_data')
                ->where('id', $request->item_id)
                ->update([
                    'item_code' => $request->editproduct,
                    'updated_at' => Carbon::now(),
                    ]);


            Toastr::success("record {$request->editproduct} updated successfully",'Success');
            return redirect()->back();

        } catch (\Exception $e) {
            Toastr::error($e->getMessage(),'Error!');
            return back()
                ->withInput();
        }
    }

    public function loadSlaughterDataAjax(Request $request, Helpers $helpers)
    {
        $baconers = DB::table('slaughter_data')
            ->whereDate('created_at', $helpers->getButcheryDate())
            ->where('item_code', 'G0110')
            ->count();

        $sows = DB::table('slaughter_data')
            ->whereDate('created_at', Carbon::parse($request->date))
            ->where('item_code', 'G0111')
            ->count();

        $data = array('baconers'=>$baconers, 'sows'=>$sows);

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

        $products = DB::table('products')
            ->get();

        $deboning_data = DB::table('deboned_data')
            ->whereDate('deboned_data.created_at', Carbon::today())
            ->leftJoin('product_types', 'deboned_data.product_type', '=', 'product_types.code')
            ->leftJoin('processes', 'deboned_data.process_code', '=', 'processes.process_code')
            ->select('deboned_data.*', 'product_types.description AS product_type', 'processes.process')
            ->get();

        $product_types = DB::table('product_types')
            ->get();

        $processes = DB::table('processes')
            ->where('process_code', '>=', 4)
            ->get();

        return view('butchery.scale3', compact('title', 'products', 'configs', 'deboning_data', 'helpers', 'product_types', 'processes'));
    }

    public function saveScaleThreeData(Request $request, Helpers $helpers)
    {
        try {
            $product = 1;
            if ($request->product_type == "By Product") {
                $product = 2;
            }
            $process_code = $helpers->getProcessCode($request->process_type);
            # insert record
            $new = DebonedData::create([
                'item_code' =>  $request->product,
                'actual_weight' => $request->reading,
                'net_weight' => $request->net,
                'process_code' => (int)$process_code,
                'product_type' => $product,
                'user_id' => Auth::id(),
            ]);

            Toastr::success("record {$request->product} inserted successfully",'Success');
            return redirect()->back();

        } catch (\Exception $e) {
            Toastr::error($e->getMessage(),'Error!');
            return back()
                ->withInput();
        }

    }

    public function getProductTypeAjax(Request $request)
    {
        $data = DB::table('products')->where('code', $request->product_code)->first();
        return response()->json($data);

    }

    public function products()
    {
        $title = "products";

        $products = DB::table('products')
            ->where('code', '!=', '')
            ->get();

        return view('butchery.products', compact('title', 'products'));
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

    public function loadSplitData(Request $request){
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
            DB::transaction(function () use($request, $helpers) {
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

            Toastr::success("record {$request->item_name} splitted successfully",'Success');
            return redirect()->back();

        } catch (\Exception $e) {
            Toastr::error($e->getMessage(),'Error!');
            return back()
                ->withInput();
        }

    }

    public function addProduct(Request $request)
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
            'user_id' => Auth::id(),

        ]);

        Toastr::success("product {$request->product} inserted successfully",'Success');
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

    public function getSalesReport(Helpers $helpers)
    {
        $title = "Sales-Report";
        $sales_data = DB::table('sales')
            ->leftJoin('products', 'sales.item_code', '=', 'products.code')
            ->leftJoin('processes', 'sales.process_code', '=', 'processes.process_code')
            ->select('sales.*', 'products.description', 'processes.process')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('butchery.sales', compact('title', 'sales_data', 'helpers'));

    }
}
