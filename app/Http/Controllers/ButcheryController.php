<?php

namespace App\Http\Controllers;

use App\Models\BeheadingData;
use App\Models\ButcheryData;
use App\Models\Helpers;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SlicingData;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ButcheryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $title = "dashboard";

        $baconers = BeheadingData::whereDate('created_at', Carbon::today())
            ->where('item_code', "G0110")
            ->sum('no_of_carcass');

        $sows = BeheadingData::whereDate('created_at', Carbon::today())
            ->where('item_code', "G0111")
            ->sum('no_of_carcass');

        $baconers_weight = BeheadingData::whereDate('created_at', Carbon::today())
            ->where('item_code', "G0110")
            ->sum('net_weight');

        $sows_weight = BeheadingData::whereDate('created_at', Carbon::today())
            ->where('item_code', "G0111")
            ->sum('net_weight');

        return view('butchery.dashboard', compact('title', 'baconers', 'sows', 'baconers_weight', 'sows_weight'));
    }

    public function scaleOneAndTwo()
    {
        $title = "Scale-1&2";

        $configs = DB::table('scale_configs')
            ->where('section', 'butchery')
            ->select('scale', 'tareweight', 'comport')
            ->get()->toArray();

        $products = DB::table('products')
            ->orWhere('description', 'Legs')
            ->orWhere('description', 'Middles')
            ->orWhere('description', 'Shoulders')
            ->orderBy('code', 'ASC')
            // ->get()->toArray();
            ->get();

        $beheading_data = DB::table('beheading_data')
            ->leftJoin('carcass_types', 'beheading_data.item_code', '=', 'carcass_types.code')
            ->select('beheading_data.*', 'carcass_types.description')
            ->get();

        $butchery_data = DB::table('butchery_data')
            ->leftJoin('products', 'butchery_data.item_code', '=', 'products.code')
            ->select('butchery_data.*', 'products.description')
            ->get();

        $carcass_types = DB::table('carcass_types')
            ->get();

        return view('butchery.scale1-2', compact('title', 'configs', 'products', 'beheading_data', 'butchery_data', 'carcass_types'));
    }

    public function saveScaleOneData(Request $request)
    {
        try {
            // insert sales substr($string, 0, -1);
            if ($request->carcass_type == "G0110A" || $request->carcass_type == "G0110B") {
                $new = Sale::create([
                    'item_code' => "G0110",
                    'no_of_carcass' => $request->no_of_carcass,
                    'net_weight' => $request->net,
                    'user_id' => Auth::id(),
                ]);

                Toastr::success('sale recorded successfully','Success');
                return redirect()->back();
            }
            // insert beaheding data
            $new = BeheadingData::create([
                'item_code' => $request->carcass_type,
                'no_of_carcass' => $request->no_of_carcass,
                'net_weight' => $request->net,
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
            $new = ButcheryData::create([
                'carcass_type' =>  $request->carcass_type,
                'item_code' =>  $request->item_code,
                'net_weight' => $request->net2,
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

    public function loadSlaughterDataAjax(Request $request)
    {
        $baconers = DB::table('slaughter_data')
            ->whereDate('created_at', Carbon::parse($request->date))
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

        $slicing_data = DB::table('slicing_data')
            ->get();

        return view('butchery.scale3', compact('title', 'products', 'configs', 'slicing_data', 'helpers'));
    }

    public function saveScaleThreeData(Request $request)
    {
        try {
            # insert record
            $new = SlicingData::create([
                'item_code' =>  $request->product,
                'net_weight' => $request->net,
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

    public function products()
    {
        $title = "products";

        $products = DB::table('products')
            ->get();

        return view('butchery.products', compact('title', 'products'));
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
}
