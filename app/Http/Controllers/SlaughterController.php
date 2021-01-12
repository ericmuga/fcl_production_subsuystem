<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use App\Models\SlaughterData;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SlaughterController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $title = "dashboard";
        $slaughtered = DB::table('slaughter_data')
            ->whereDate('created_at', Carbon::today())
            ->count();

        $lined_up = DB::table('receipts')
            ->whereDate('slaughter_date', Carbon::yesterday())
            ->sum('receipts.received_qty');

        return view('slaughter.dashboard', compact('title', 'slaughtered', 'lined_up'));
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
            ->orderBy('created_at', 'DESC')
            ->get();

        $slaughter_data = DB::table('slaughter_data')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('slaughter.weigh', compact('title', 'configs', 'receipts', 'slaughter_data'));
    }

    public function loadWeighDataAjax(Request $request)
    {
        $data = DB::table('receipts')
            ->where('receipt_no', $request->receiptNo)
            ->where('vendor_tag', $request->slapmark)
            ->select('item_code', 'vendor_no', 'vendor_name')
            ->first();

        return response()->json($data);
    }

    public function saveWeighData(Request $request)
    {
        try {
            //code...
            $new = new SlaughterData();
            $new->receipt_no = $request->receipt_no;
            $new->slapmark = $request->slapmark;
            $new->item_code = $request->slapmark;
            $new->vendor_no = $request->vendor_no;
            $new->vendor_name = $request->vendor_name;
            $new->net_weight = $request->net;
            $new->vendor_name = $request->vendor_name;
            $new->meat_percent = $request->meat_percent;
            $new->classification_code = $request->classification_code;
            $new->user_id = Auth::id();
            $new->save();

            Toastr::success('record added successfully','Success');
            return redirect()->back();

        } catch (\Exception $e) {
            Toastr::error($e->getMessage(),'Error!');
            return back()
                ->withInput();
        }
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
