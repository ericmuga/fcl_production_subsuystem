<?php

namespace App\Http\Controllers;

use App\Exports\DespatchIdtHistoryExport;
use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class DespatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check')->except(['']);
    }

    public function index()
    {
        $title = "dashboard";

        $transfers = DB::table('idt_transfers')
            ->whereDate('idt_transfers.created_at', today())
            ->whereIn('idt_transfers.transfer_from', ['2055', '2595', '1570', '2500'])
            ->select('transfer_from', DB::raw('SUM(idt_transfers.receiver_total_pieces) as total_pieces'), DB::raw('SUM(idt_transfers.receiver_total_weight) as total_weight'), DB::raw('SUM(idt_transfers.total_pieces) as issued_pieces'), DB::raw('SUM(idt_transfers.total_weight) as issued_weight'))
            ->groupBy('idt_transfers.transfer_from')
            ->get()->groupBy('transfer_from');

        return view('despatch.dashboard', compact('title', 'transfers'));
    }

    public function getIdt(Helpers $helpers, $filter = null)
    {
        $title = "IDT";

        $configs = Cache::remember('despatch_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('section', 'despatch')
                ->select('scale', 'tareweight', 'comport')
                ->get()->toArray();
        });

        $items = Cache::remember('items_list', now()->addHours(10), function () {
            return DB::table('items')
                ->select('code', 'barcode', 'description', 'qty_per_unit_of_measure', 'unit_count_per_crate')
                ->get();
        });

        $username = Session::get('session_userName');

        $query = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->leftJoin('users', 'idt_transfers.user_id', '=', 'users.id')
            ->select('idt_transfers.*', 'items.description as product', 'items.qty_per_unit_of_measure', 'items.unit_count_per_crate', 'users.username')
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->where('idt_transfers.received_by', '=', null)
            ->where('idt_transfers.total_weight', '>', '0.0') // not cancelled
            ->whereDate('idt_transfers.created_at', '>=', today()->subDays(($username == 'pnjuguna' || $username == 'skinyua') ? 15 : 1)) // 15 days supervisors like pngjuguna, others 1 day back only.
            ->when($filter == 'sausage', function ($q) {
                $q->where('idt_transfers.transfer_from', '=', '2055'); // from sausage only
            })
            ->when($filter == 'highcare', function ($q) {
                $q->where('idt_transfers.transfer_from', '=', '2595') // from highcare and not bulk only
                    ->where('idt_transfers.filter1', null);
            })
            ->when($filter == 'highcare_bulk', function ($q) {
                $q->where('idt_transfers.filter1', 'bulk')
                    ->where(function ($q) {
                        $q->where('idt_transfers.transfer_from', '=', '2595')
                            ->orWhere('idt_transfers.transfer_from', '2500');
                    }); // from highcare and bulk only
            })
            ->when($filter == 'fresh_cuts', function ($q) {
                $q->where('idt_transfers.transfer_from', '=', '1570'); // from butchery only
            });

        $transfer_lines = $query->get();

        return view('despatch.idt', compact('title', 'transfer_lines', 'items', 'configs', 'helpers'));
    }

    public function receiveTransfer(Request $request, Helpers $helpers)
    {
        $validator = Validator::make($request->all(), [
            'crates_valid' => 'required|boolean',
            'item_id' => 'required'
        ]);

        if ($validator->fails()) {
            # failed validation
            $messages = $validator->errors();
            foreach ($messages->all() as $message) {
                Toastr::error($message, 'Error!');
            }
            return back();
        }

        try {
            // try update
            DB::table('idt_transfers')
                ->where('id', $request->item_id)
                ->update([
                    'chiller_code' => $request->chiller_code,
                    'receiver_total_crates' => $request->total_crates,
                    'receiver_full_crates' => $request->full_crates,
                    'receiver_incomplete_crate_pieces' => $request->incomplete_pieces,
                    'receiver_total_pieces' => $request->pieces,
                    'receiver_total_weight' => $request->weight,
                    'received_by' => $helpers->authenticatedUserId(),
                    'with_variance' => $request->valid_match,
                    'updated_at' => now(),
                ]);

            Toastr::success('IDT Transfer received successfully', 'Success');
            return redirect()
                ->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function receiveTransferFreshcuts(Request $request, Helpers $helpers)
    {
        try {
            // try update
            DB::table('idt_transfers')
                ->where('id', $request->item_id)
                ->update([
                    'chiller_code' => $request->chiller_code,
                    'receiver_total_pieces' => $request->f_no_of_pieces,
                    'receiver_total_weight' => $request->net,
                    'received_by' => $helpers->authenticatedUserId(),
                    'with_variance' => $request->valid_match,
                    'updated_at' => now(),
                ]);

            Toastr::success('IDT Transfer received successfully', 'Success');
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
        $title = "IDT-Report";

        $days_filter = 20;

        $transfer_lines = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->leftJoin('users', 'idt_transfers.received_by', '=', 'users.id')
            ->select('idt_transfers.*', 'items.description as product', 'items.qty_per_unit_of_measure', 'items.unit_count_per_crate', 'users.username')
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->when($filter == 'today', function ($q) {
                $q->whereDate('idt_transfers.created_at', today()); // today only
            })
            ->when($filter == 'history', function ($q, $days_filter) {
                $q->whereDate('idt_transfers.created_at', '>=', today()->subDays(20)); // today plus last 7 days
            })
            ->get();

        return view('despatch.idt-report', compact('title', 'filter', 'transfer_lines', 'helpers', 'days_filter'));
    }

    public function exportIdtHistory(Request $request)
    {
        $from_date = Carbon::parse($request->from_date);
        $to_date = Carbon::parse($request->to_date);
        $has_variance = 0;
        $ext = '.xlsx';

        $entries = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->leftJoin('users', 'idt_transfers.received_by', '=', 'users.id')
            ->whereDate('idt_transfers.created_at', '>=', $from_date)
            ->whereDate('idt_transfers.created_at', '<=', $to_date)
            ->where('idt_transfers.transfer_from', $request->transfer_from)
            ->select('idt_transfers.id', 'idt_transfers.product_code', 'items.description as product', 'items.qty_per_unit_of_measure', 'idt_transfers.location_code', 'idt_transfers.transfer_from', 'idt_transfers.description as customer_code', 'idt_transfers.order_no', 'idt_transfers.total_pieces', 'idt_transfers.total_weight', 'idt_transfers.receiver_total_pieces', 'idt_transfers.receiver_total_weight', DB::raw("(CASE WHEN idt_transfers.with_variance = '0' THEN 'Yes' ELSE 'No' END) AS with_variance"), 'idt_transfers.batch_no', 'users.username as received_by', 'idt_transfers.created_at')
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->get();

        $exports = Session::put('session_export_data', $entries);

        return Excel::download(new DespatchIdtHistoryExport, "IdtHistoryFor {$request->transfer_from} from- {$request->from_date} to {$request->to_date} $ext");
    }

    public function idtVarianceReport($filter = null)
    {
        $title = "IDT-Variance Report";

        $variance_lines = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->select('idt_transfers.product_code', DB::raw('SUM(idt_transfers.total_pieces) as issued_pieces'), DB::raw('SUM(idt_transfers.total_weight) as issued_weight'), DB::raw('SUM(idt_transfers.receiver_total_pieces) as received_pieces'), DB::raw('SUM(idt_transfers.receiver_total_weight) as received_weight'), 'items.description as product')
            ->orderBy('idt_transfers.product_code', 'ASC')
            ->whereDate('idt_transfers.created_at', today())
            ->when($filter == 'sausage', function ($q) {
                $q->where('idt_transfers.transfer_from', '2055');
            })
            ->when($filter == 'highcare', function ($q) {
                $q->where('idt_transfers.transfer_from', '2595')
                    ->orWhere('idt_transfers.transfer_from', '2500');
            })
            ->having(DB::raw('COALESCE(SUM(idt_transfers.total_weight), 0)'), '!=', DB::raw('COALESCE(SUM(idt_transfers.receiver_total_weight), 0)'))
            ->groupBy('idt_transfers.product_code', 'items.description')
            ->get();

        return view('despatch.idt-variance', compact('title', 'variance_lines'));
    }

    public function idtStocksPerChiller()
    {
        $title = "IDT-Variance Report";

        $items = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->select('idt_transfers.product_code', 'idt_transfers.location_code', DB::raw('COALESCE(SUM(idt_transfers.receiver_total_pieces), 0) as received_pieces'), DB::raw('COALESCE(SUM(idt_transfers.receiver_total_weight), 0) as received_weight'), 'items.description as product')
            ->orderBy('idt_transfers.product_code', 'ASC')
            ->whereDate('idt_transfers.created_at', today())
            ->groupBy('idt_transfers.product_code', 'items.description', 'idt_transfers.location_code')
            ->get();

        return view('despatch.idt-per-chiller', compact('title', 'items'));
    }
}
