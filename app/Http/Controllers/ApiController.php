<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public function getSlaughterData(Request $request)
    {
        $from_date = Carbon::parse($request->from_date);
        $to_date = Carbon::parse($request->to_date);

        $columns = [
            'receipt_no', 'slapmark', 'item_code', 'vendor_no', 'vendor_name', 
            'actual_weight', 'net_weight', 'meat_percent', 'settlement_weight', 
            'classification_code', 'created_at'
        ];

        $slaughter_data = DB::table('slaughter_data as a')
            ->whereDate('a.created_at', '>=', $from_date)
            ->whereDate('a.created_at', '<=', $to_date)
            ->select($columns)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($slaughter_data);
    }

    public function missingSlapData(Request $request)
    {
        $from_date = Carbon::parse($request->from_date);
        $to_date = Carbon::parse($request->to_date);

        $columns = [
            'a.slapmark', 'a.item_code', 'a.actual_weight', 'a.net_weight', 'a.settlement_weight','a.meat_percent','a.classification_code','b.username as created_by', 'a.created_at'
        ];

        $slaps = DB::table('missing_slap_data as a')
            ->whereDate('a.created_at', '>=', $from_date)
            ->whereDate('a.created_at', '<=', $to_date)
            ->join('users as b', 'a.user_id', '=', 'b.id')
            ->select($columns)
            ->orderByDesc('a.created_at')
            ->get();

        return response()->json($slaps);
    }
}
