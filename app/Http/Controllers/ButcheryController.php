<?php

namespace App\Http\Controllers;

use App\Models\BeheadingData;
use App\Models\ButcheryData;
use App\Models\Sale;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ButcheryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $title = "dashboard";

        return view('butchery.dashboard', compact('title'));
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

    public function scaleThree()
    {
        $title = "Scale-3";

        $products = DB::table('products')
            ->orderBy('code', 'ASC')
            ->get();

        return view('butchery.scale3', compact('title', 'products'));
    }

    public function products()
    {
        $title = "products";
        return view('butchery.products', compact('title'));
    }

    public function scaleSettings()
    {
        $title = "Scale";
        return view('butchery.scale_settings', compact('title'));
    }

    public function changePassword()
    {
        $title = "password";
        return view('butchery.change_password', compact('title'));
    }
}
