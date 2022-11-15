<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HighCare1Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check')->except([]);
    }

    public function index()
    {
        $title = "Todays-Entries";

        return view('highcare1.dashboard', compact('title'));
    }

    public function getIdt(Helpers $helpers)
    {
        $title = "Todays-Entries";

        $items = Cache::remember('items_list_sausage', now()->addHours(10), function () {
            return DB::table('items')
                ->where('blocked', '!=', 1)
                ->select('code', 'barcode', 'description', 'qty_per_unit_of_measure', 'unit_count_per_crate')
                ->get();
        });

        $transfer_lines = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->leftJoin('users', 'idt_transfers.received_by', '=', 'users.id')
            ->select('idt_transfers.*', 'items.description as product', 'items.qty_per_unit_of_measure', 'items.unit_count_per_crate', 'users.username')
            ->whereDate('idt_transfers.created_at', today())
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->get();

        return view('highcare1.idt', compact('title', 'items', 'transfer_lines', 'helpers'));
    }
}
