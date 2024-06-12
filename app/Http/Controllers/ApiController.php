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
}
