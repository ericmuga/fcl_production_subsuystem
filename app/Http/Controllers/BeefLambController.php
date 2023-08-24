<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        $title = 'Beef Slicing';

        $configs = Cache::remember('beef_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('section', 'beef')
                ->where('scale', 'beef')
                ->select('scale', 'tareweight', 'comport')
                ->get()->toArray();
        });

        $products = DB::table('beef_product_processes')
            ->leftJoin('beef_items', 'beef_product_processes.product_code', '=', 'beef_items.code')
            ->leftJoin('processes', 'beef_product_processes.process_code', '=', 'processes.process_code')
            ->leftJoin('product_types', 'beef_product_processes.product_type', '=', 'product_types.code')
            ->select('beef_product_processes.product_code', 'beef_product_processes.process_code', 'beef_product_processes.product_type', 'beef_items.description', 'processes.shortcode', 'processes.process', 'product_types.description as type_description')
            ->get();

        // dd($products);

        return view('beef_lamb.deboning_beef', compact('title', 'products', 'configs'));
    }
}
