<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SlaughterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $title = "dashboard";
        return view('slaughter.dashboard', compact('title'));
    }

    public function weigh()
    {
        $title = "weigh";
        $configs = DB::table('scale_configs')
            ->where('scale', 'Scale 1')
            ->where('section', 'slaughter')
            ->select('tareweight', 'comport')
            ->get()->toArray();

        $receipts = DB::table('receipts')
            ->get();
        return view('slaughter.weigh', compact('title', 'configs', 'receipts'));
    }

    public function loadWeighDataAjax(Request $request)
    {
        $data = DB::table('receipts')
            ->where('receipt_no', $request->receiptNo)
            ->where('vendor_tag', $request->slapmark)
            ->select('item_code', 'vendor_no', 'vendor_name')
            ->first();
        // return response()->json($centres);
        // $data = array('receipt_no' => $request->receiptNo, 'slapmark'=> $request->slapmark);
        return response()->json($data);
    }

    public function import()
    {
        $title = "import";
        return view('slaughter.import', compact('title'));

    }

    public function importedReceipts()
    {
        $title = "receipts";
        return view('slaughter.receipts', compact('title'));

    }

    public function slaughterDataReport()
    {
        $title = "receipts";
        return view('slaughter.receipts', compact('title'));

    }

    public function scaleSettings()
    {
        $title = "scale";
        return view('slaughter.scale_settings', compact('title'));
    }

    public function changePassword()
    {
        $title = "password";
        return view('slaughter.change_password', compact('title'));
    }
}
