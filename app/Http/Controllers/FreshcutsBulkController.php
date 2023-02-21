<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FreshcutsBulkController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check');
    }
    public function index()
    {
        $title = "dashboard";

        return view('fresh_bulk.dashboard', compact('title'));
    }

    public function getIdt()
    {
        $title = "IDT";

        $configs = Cache::remember('freshcuts_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('section', 'freshcuts')
                ->select('scale', 'tareweight', 'comport')
                ->get()->toArray();
        });

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
            ->where('idt_transfers.transfer_from', '2595')
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->get();

        return view('fresh_bulk.idt', compact('title', 'items', 'transfer_lines', 'configs'));
    }

    public function idtReport(Helpers $helpers, $filter = null)
    {
        $title = "IDT Report";

        switch ($filter) {
            case 'today':
                $range_filter = 'Todays Transfers';
                break;

            default:
                # code...
                $range_filter = 'Last 7 days';
                break;
        }

        $transfer_lines = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->leftJoin('users', 'idt_transfers.received_by', '=', 'users.id')
            ->select('idt_transfers.*', 'items.description as product', 'items.qty_per_unit_of_measure', 'items.unit_count_per_crate', 'users.username')
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->when($filter == 'today', function ($q) {
                $q->whereDate('idt_transfers.created_at', today()); // today only
            })
            ->when($filter == '', function ($q) {
                $q->whereDate('idt_transfers.created_at', '>=', today()->subDays(7)); // today plus last 7 days
            })
            ->get();

        return view('fresh_bulk.idt-report', compact('title', 'transfer_lines', 'helpers', 'range_filter'));
    }
}
