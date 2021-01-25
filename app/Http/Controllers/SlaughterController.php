<?php

namespace App\Http\Controllers;

use App\Imports\ReceiptsImport;
use App\Models\Helpers;
use App\Models\MissingSlapData;
use App\Models\SlaughterData;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
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
            ->whereDate('slaughter_date', Carbon::today())
            ->sum('receipts.received_qty');

        $missing_slaps = DB::table('missing_slap_data')
            ->whereDate('created_at', Carbon::today())
            ->count();

        $carcass_types = DB::table('carcass_types')
            ->orderBy('code', 'asc')
            ->get();

        return view('slaughter.dashboard', compact('title', 'slaughtered', 'lined_up', 'missing_slaps', 'carcass_types'));
    }

    public function weigh(Helpers $helpers)
    {
        $title = "weigh";
        $configs = DB::table('scale_configs')
            ->where('scale', 'Scale 1')
            ->where('section', 'slaughter')
            ->select('tareweight', 'comport')
            ->get()->toArray();

        $receipts = DB::table('receipts')
            ->whereDate('slaughter_date', Carbon::today())
            ->get();

        $slaughter_data = DB::table('slaughter_data')
            ->whereDate('created_at', Carbon::today())
            ->orderBy('created_at', 'DESC')
            ->get();

        $carcass_types = DB::table('carcass_types')
            ->get();

        $slaps = DB::table('missing_slap_data')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('slaughter.weigh', compact('title', 'configs', 'receipts', 'slaughter_data', 'helpers', 'carcass_types', 'slaps'));
    }

    public function loadWeighDataAjax(Request $request)
    {
        $data = DB::table('receipts')
            ->where('vendor_tag', $request->slapmark)
            ->select('receipt_no', 'item_code', 'vendor_no', 'vendor_name')
            ->first();

        return response()->json($data);
    }

    public function loadWeighMoreDataAjax(Request $request)
    {
        $total_per_vendor = DB::table('receipts')
            ->whereDate('slaughter_date', Carbon::today())
            ->where('vendor_tag', $request->slapmark)
            ->sum('receipts.received_qty');

        $total_weighed = DB::table('slaughter_data')
            ->whereDate('created_at', Carbon::today())
            ->where('slapmark', $request->slapmark)
            ->count();

        $dataArray = array('total_per_vendor' => $total_per_vendor, 'total_weighed' => $total_weighed);

        return response()->json($dataArray);

    }

    public function saveWeighData(Request $request)
    {
        try {
            // try save
            $new = new SlaughterData();
            $new->receipt_no = $request->receipt_no;
            $new->slapmark = $request->slapmark;
            $new->item_code = $request->carcass_type;
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

    public function saveMissingSlapData(Request $request)
    {
        try {
            // try save
            $new = new MissingSlapData();
            $new->slapmark = $request->ms_slap;
            $new->item_code = $request->ms_carcass_type;
            $new->net_weight = $request->ms_net;
            $new->meat_percent = $request->ms_meat_pc;
            $new->classification_code = $request->ms_classification;
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

    public function missingSlapData(Request $request, Helpers $helpers)
    {
        $title = "SlapData";
        $slaps = DB::table('missing_slap_data')
            ->get();
        return view('slaughter.missing_slapmarks', compact('title', 'slaps', 'helpers'));

    }

    public function importedReceipts(Helpers $helpers)
    {
        $title = "receipts";
        $receipts = DB::table('receipts')
            ->orderBy('slaughter_date', 'ASC')
            ->get();
        return view('slaughter.receipts', compact('title', 'receipts', 'helpers'));

    }

    public function importReceipts(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt',
            'slaughter_date' => 'required',

        ]);

        if ($validator->fails()) {
            # failed validation
            $messages = $validator->errors();
            foreach ($messages->all() as $message) {
                Toastr::error($message, 'Error!');
            }
            return back();
        }

        // upload
        $database_date = Carbon::parse($request->slaughter_date);
        Session::put('slaughter_date', $database_date);
        Excel::import(new ReceiptsImport, request()->file('file'));

        Toastr::success('receipts uploaded successfully', 'Success');
        return redirect()->back();
    }

    public function slaughterDataReport(Helpers $helpers)
    {
        $title = "receipts";
        $slaughter_data = DB::table('slaughter_data')
            ->orderBy('created_at', 'DESC')
            ->get();

        return view('slaughter.slaughter_report', compact('title', 'helpers', 'slaughter_data'));

    }

    public function scaleSettings(Helpers $helpers)
    {
        $title = "scale";
        $scale_settings = DB::table('scale_configs')
            ->where('section', 'slaughter')
            ->get();
        return view('slaughter.scale_settings', compact('title', 'scale_settings', 'helpers'));
    }

    public function changePassword()
    {
        $title = "password";
        return view('slaughter.change_password', compact('title'));
    }
}
