<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Helpers;
use Illuminate\Http\Request;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DataController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function listItems(Helpers $helpers)
    {
        $title = 'Items';
        $items = DB::table('items')->get();
        return view('butchery.items', compact('title', 'items', 'helpers'));
    }

    public function beefListItems(Helpers $helpers)
    {
        $title = 'Items';

        $items = DB::table('beef_lamb_items')->get();

        return view('beef_lamb.items', compact('title', 'items', 'helpers'));
    }

    // Show the form for creating a new resource.
    public function createItem(Request $request)
    {
        // Log::info($request->all());
        try {
                DB::table('items')->insert([
                    'code' => $request->code,
                    'barcode' => $request->barcode,
                    'description' => $request->description,
                    'unit_of_measure' => $request->unit_of_measure,
                    'qty_per_unit_of_measure' => $request->qty_per_unit_of_measure,
                    'unit_count_per_crate' => $request->unit_count_per_crate,
                ]);

            if ($request->despatch_combo == 'on') {
                DB::table('item_location_combinations')->insert([
                    'item_code' => $request->code,
                    'location' => '3535',
                ]);
            }

            Toastr::success('Item created', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()->withInput();
        }
    }

    public function createBeefItem(Request $request)
    {
        // Log::info($request->all());
        try {
                DB::table('beef_lamb_items')->insert([
                    'code' => $request->code,
                    'barcode' => $request->barcode,
                    'description' => $request->description,
                    'unit_of_measure' => $request->unit_of_measure,
                    'location_code' => '1570',
                ]);

            if ($request->despatch_combo == 'on') {
                DB::table('item_location_combinations')->insert([
                    'item_code' => $request->code,
                    'location' => '3535',
                ]);
            }

            Toastr::success("Item '{$request->description}' created", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()->withInput();
        }
    }
}