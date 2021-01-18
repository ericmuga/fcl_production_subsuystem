<?php

namespace App\Http\Controllers;

use App\Models\BeheadingData;
use App\Models\ButcheryData;
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
            ->orWhere('product', 'Legs')
            ->orWhere('product', 'Middles')
            ->orWhere('product', 'Shoulders')
            ->orderBy('code', 'ASC')
            // ->get()->toArray();
            ->get();

        $beheading_data = DB::table('beheading_data')
            ->get();

        $butchery_data = DB::table('butchery_data')
            ->leftJoin('products', 'butchery_data.item_code', '=', 'products.code')
            ->select('butchery_data.*', 'products.product')
            ->get();

        return view('butchery.scale1-2', compact('title', 'configs', 'products', 'beheading_data', 'butchery_data'));
    }

    public function saveScaleOneData(Request $request)
    {
        try {
            //check if exist
            $exist = BeheadingData::whereDate('created_at', Carbon::today())->first();
            if ($exist != null){
                //record exists, check what is to be updated
                if ($request->carcass_type == "baconers") {
                    # baconers
                    $exist->increment('baconers', $request->no_of_carcass);

                } else {
                    //update sows
                    $exist->increment('sows', $request->no_of_carcass);

                }

                Toastr::success('record updated successfully','Success');
                return redirect()->back();

            } else {
                // insert baconers
                if ($request->carcass_type == "baconers") {
                    $new = BeheadingData::create([
                        'baconers' =>  $request->no_of_carcass,
                        'sows' => 0,
                        'user_id' => Auth::id(),
                    ]);

                } else {
                    // insert sows
                    $new = BeheadingData::create([
                        'baconers' =>  0,
                        'sows' => $request->no_of_carcass,
                        'user_id' => Auth::id(),
                    ]);

                }

                Toastr::success('record inserted successfully','Success');
                return redirect()->back();
            }

        } catch (\Exception $e) {
            Toastr::error($e->getMessage(),'Error!');
            return back()
                ->withInput();
        }

    }

    public function saveScaleTwoData(Request $request){
        try {
            //check if exist
            // $exist = ButcheryData::whereDate('created_at', Carbon::today())
            //     ->where('item_code', $request->item_code)
            //     ->first();

            // if ($exist != null){
            //     //record exists, updated
            //     $exist->increment('net_weight', $request->net2);

            //     Toastr::success('record updated successfully','Success');
            //     return redirect()->back();

            // }

            # insert record
            $new = ButcheryData::create([
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
        // dd($request->all());
        try {
            //update
            DB::table('butchery_data')
                ->where('id', $request->id)
                ->update(['item_code' => $request->editproduct]);

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
        return view('butchery.scale3', compact('title'));
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
