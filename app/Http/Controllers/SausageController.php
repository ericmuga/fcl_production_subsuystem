<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mockery\Matcher\Type;

class SausageController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check');
    }

    public function index()
    {
        $title = "Dashboard";

        $total_tonnage = DB::table('sausage_entries')
            // ->where('sausage_entries.created_at', today())
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->sum(DB::raw('1 * items.qty_per_unit_of_measure'));

        $total_entries =  DB::table('sausage_entries')
            // ->where('sausage_entries.created_at', today())
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->count('sausage_entries.barcode');

        $highest_product = DB::table('sausage_entries')
            // ->where('sausage_entries.created_at', today())
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->where('items.code', '!=', null)
            ->select('sausage_entries.barcode', 'items.code', 'items.description', DB::raw('COUNT(sausage_entries.barcode) as total_count'), 'items.qty_per_unit_of_measure')
            ->groupBy('sausage_entries.barcode', 'items.code', 'items.description', 'items.qty_per_unit_of_measure')
            ->orderBy('total_count', 'DESC')
            ->limit(1)
            ->get()->toArray();

        $lowest_product = DB::table('sausage_entries')
            // ->where('sausage_entries.created_at', today())
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->where('items.code', '!=', null)
            ->select('sausage_entries.barcode', 'items.code', 'items.description', DB::raw('COUNT(sausage_entries.barcode) as total_count'), 'items.qty_per_unit_of_measure')
            ->groupBy('sausage_entries.barcode', 'items.code', 'items.description', 'items.qty_per_unit_of_measure')
            ->orderBy('total_count', 'ASC')
            ->limit(1)
            ->get()->toArray();
        // dd($lowest_product);

        $wrong_entries =  DB::table('sausage_entries')
            // ->where('sausage_entries.created_at', today())
            ->where('items.code', null)
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->count('sausage_entries.barcode');

        return view('sausage.dashboard', compact('title', 'total_tonnage', 'total_entries', 'highest_product', 'lowest_product', 'wrong_entries'));
    }

    public function productionEntries()
    {
        $title = "Todays-Entries";

        $entries = DB::table('sausage_entries')
            // ->where('sausage_entries.created_at', today())
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->select('sausage_entries.barcode', 'items.code', 'items.description', DB::raw('COUNT(sausage_entries.barcode) as total_count'), 'items.qty_per_unit_of_measure')
            ->groupBy('sausage_entries.barcode', 'items.code', 'items.description', 'items.qty_per_unit_of_measure')
            ->orderBy('total_count', 'DESC')
            ->get();

        return view('sausage.entries', compact('entries', 'title'));
    }

    public function itemsList()
    {
        $title = "Items-List";
        $category = "JF-SAUSAGE";

        $items = Cache::remember('items_list', now()->addMinutes(480), function () use ($category) {
            return DB::table('items')
                ->where('category', $category)
                ->get();
        });

        return view('sausage.items', compact('title', 'category', 'items'));
    }
}
