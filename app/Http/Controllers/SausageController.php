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

        return view('sausage.dashboard', compact('title'));
    }

    public function productionEntries()
    {
        $title = "Todays-Entries";

        $entries = DB::table('sausage_entries')
            // ->where('created_at', today())
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->select('sausage_entries.barcode', 'items.code', 'items.description', DB::raw('COUNT(sausage_entries.barcode) as total_count'))
            ->groupBy('sausage_entries.barcode', 'items.code', 'items.description')
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
