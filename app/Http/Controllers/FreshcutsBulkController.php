<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
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

    public function getIdt(Helpers $helpers)
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
            ->where('idt_transfers.transfer_from', '1570')
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->get();

        return view('fresh_bulk.idt', compact('title', 'items', 'transfer_lines', 'configs', 'helpers'));
    }

    public function createIdt(Request $request, Helpers $helpers)
    {
        try {
            // try save
            DB::table('idt_transfers')->insert([
                'product_code' => $request->product,
                'location_code' => $request->transfer_to, //transfer to location
                'chiller_code' => $request->chiller_code,
                'total_pieces' => $request->no_of_pieces,
                'total_weight' => $request->net,
                'total_crates' => $request->no_of_crates ?: 0,
                'full_crates' => $request->no_of_crates ?: 0,
                'incomplete_crate_pieces' => $request->no_of_crates ?: 0,
                'transfer_type' => $request->transfer_type,
                'transfer_from' => '1570',
                'description' => $request->desc,
                'order_no' => $request->order_no,
                'batch_no' => $request->batch_no,
                'user_id' => $helpers->authenticatedUserId(),
            ]);

            Toastr::success('IDT Transfer recorded successfully', 'Success');
            return redirect()
                ->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
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
            ->where('idt_transfers.transfer_from', '1570')
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
