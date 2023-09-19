<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BeefLambController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check');
    }

    public function index(Helpers $helpers)
    {
        $title = "dashboard";

        $layout = 'beef';

        return view('beef_lamb.dashboard', compact('title', 'layout', 'helpers'));
    }

    public function getBeefSlicing()
    {
        $title = 'Beef';

        $layout = 'beef';

        $configs = Cache::remember('beef_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('section', 'beef_lamb')
                ->where('scale', 'Beef')
                ->select('scale', 'tareweight', 'comport')
                ->get()->toArray();
        });

        $products = DB::table('beef_product_processes')
            ->leftJoin('beef_items', 'beef_product_processes.product_code', '=', 'beef_items.code')
            ->leftJoin('processes', 'beef_product_processes.process_code', '=', 'processes.process_code')
            ->leftJoin('product_types', 'beef_product_processes.product_type', '=', 'product_types.code')
            ->select('beef_product_processes.product_code', 'beef_product_processes.process_code', 'beef_product_processes.product_type', 'beef_items.description', 'processes.shortcode', 'processes.process', 'product_types.description as type_description')
            ->get();

        $entries = DB::table('beef_slicing')
            ->whereDate('beef_slicing.created_at', today())
            ->join('beef_items', 'beef_slicing.item_code', '=', 'beef_items.code')
            ->join('processes', 'beef_slicing.process_code', '=', 'processes.process_code')
            ->select('beef_slicing.*', 'beef_items.description', 'processes.process')
            ->get();

        return view('beef_lamb.slicing_beef', compact('title', 'products', 'configs', 'entries', 'layout'));
    }

    public function saveBeefSlicing(Request $request, Helpers $helpers)
    {
        $parts = explode(':', $request->product);
        $manual = $request->manual_weight == 'on';

        try {
            //insert change logs
            DB::table('beef_slicing')->insert([
                'item_code' => $parts[1],
                'scale_reading' => $request->reading,
                'net_weight' => $request->net,
                'process_code' => $parts[3],
                'product_type' => $parts[4],
                'no_of_pieces' => $request->no_of_pieces ?? 0,
                'no_of_crates' => $request->total_crates,
                'black_crates' => $request->black_crates,
                'production_date' => Carbon::createFromFormat('d/m/Y', $request->prod_date),
                'location_code' => '1570',
                'manual_weight' => $manual,
                'user_id' => $helpers->authenticatedUserId(),
            ]);

            Toastr::success("Deboning beef entry : {$request->item_id} inserted successfully", 'Success');
            return redirect()
                ->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            Log::error('An exception occurred in ' . __FUNCTION__, ['exception' => $e]);
            return back();
        }
    }
}
