<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    private function parseDates(Request $request)
    {
        return [
            'from_date' => Carbon::parse($request->from_date),
            'to_date' => Carbon::parse($request->to_date),
        ];
    }

    public function getSlaughterData(Request $request)
    {
        $dates = $this->parseDates($request);

        $from_date = $dates['from_date'];
        $to_date = $dates['to_date'];

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
        $dates = $this->parseDates($request);

        $from_date = $dates['from_date'];
        $to_date = $dates['to_date'];

        $columns = [
            'a.slapmark', 'a.item_code', 'a.actual_weight', 'a.net_weight', 'a.settlement_weight','a.meat_percent','a.classification_code', 'a.created_at'
        ];

        $slaps = DB::table('missing_slap_data as a')
            ->whereDate('a.created_at', '>=', $from_date)
            ->whereDate('a.created_at', '<=', $to_date)
            ->select($columns)
            ->orderByDesc('a.created_at')
            ->get();

        return response()->json($slaps);
    }

    public function getBeheadingData(Request $request)
    {
        $dates = $this->parseDates($request);

        $from_date = $dates['from_date'];
        $to_date = $dates['to_date'];

        $columns = [
            'item_code','no_of_carcass','actual_weight','net_weight','process_code','return_entry', 'a.created_at'
        ];

        $beheading_data = DB::table('beheading_data as a')
            ->whereDate('a.created_at', '>=', $from_date)
            ->whereDate('a.created_at', '<=', $to_date)
            ->select($columns)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($beheading_data);
    }

    public function getBrakingData(Request $request)
    {
        $dates = $this->parseDates($request);

        $from_date = $dates['from_date'];
        $to_date = $dates['to_date'];

        $columns = [
            'carcass_type','item_code','actual_weight','net_weight','process_code','product_type','no_of_items', 'created_at'
        ];

        $butchery_data = DB::table('butchery_data as a')
            ->whereDate('a.created_at', '>=', $from_date)
            ->whereDate('a.created_at', '<=', $to_date)
            ->select($columns)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($butchery_data);
    }

    public function getDeboningData(Request $request)
    {
        $dates = $this->parseDates($request);

        $from_date = $dates['from_date'];
        $to_date = $dates['to_date'];

        $columns = [
            'item_code','actual_weight','net_weight','process_code','product_type','no_of_pieces','no_of_crates','splitted','narration', 'created_at'
        ];

        $deboning_data = DB::table('deboned_data as a')
            ->whereDate('a.created_at', '>=', $from_date)
            ->whereDate('a.created_at', '<=', $to_date)
            ->select($columns)
            ->orderByDesc('created_at')
            ->get();

        return response()->json($deboning_data);
    }
}
