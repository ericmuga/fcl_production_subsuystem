<?php

namespace App\Http\Controllers;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
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

        return view('butchery.scale1-2', compact('title', 'configs'));
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
