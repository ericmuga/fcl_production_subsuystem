<?php

namespace App\Http\Controllers;

use App\Exports\DespatchIdtHistoryExport;
use App\Imports\ImportStocks;
use App\Imports\ImportStocksCSV;
use App\Imports\ImportStocksExcel;
use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class DespatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['']);
    }

    public function index()
    {
        $title = "dashboard";

        $transfers = DB::table('idt_transfers')
            ->whereDate('idt_transfers.created_at', today())
            ->whereIn('idt_transfers.transfer_from', ['2055', '2595', '1570', '2500'])
            ->whereIn('idt_transfers.location_code', ['3535', '3600'])
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

        // dd($username);

        $query = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->leftJoin('users', 'idt_transfers.user_id', '=', 'users.id')
            ->select('idt_transfers.*', 'items.description as product', 'items.qty_per_unit_of_measure', 'items.unit_count_per_crate', 'users.username')
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->where('idt_transfers.received_by', '=', null)
            ->whereIn('idt_transfers.location_code', ['3535', '3600'])
            ->where('idt_transfers.total_weight', '>', '0.0') // not cancelled
            ->whereDate('idt_transfers.created_at', '>=', today()->subDays(in_array(strtolower($username), array_map('strtolower', config('app.despatch_supervisors'))) ? 20 : 2)) // 20 days for supervisors, others 2 days back only.
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

        $transfer = DB::table('idt_transfers')
            ->where('id', $request->item_id)
            ->first();

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
                    'received_by' => Auth::id(),
                    'with_variance' => $request->valid_match,
                    'updated_at' => now(),
                ]);

            $data = [
                'product_code' => $transfer->product_code,
                'transfer_from_location' => $transfer->transfer_from,
                'transfer_to_location' => $transfer->location_code,
                'receiver_total_pieces' => $request->pieces ?? 0,
                'receiver_total_weight' => $request->weight,
                'received_by' => Auth::id(),
                'production_date' => $transfer->production_date,
                'with_variance' => $request->valid_match,
                'timestamp' => now()->toDateTimeString(),
                'id' => $request->item_id,
            ];

            // Publish data to RabbitMQ
            $helpers->publishToQueue($data, 'production_data_transfer.bc');

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
        $transfer = DB::table('idt_transfers')
            ->where('id', $request->item_id)
            ->first();

        try {
            // try update
            DB::table('idt_transfers')
                ->where('id', $request->item_id)
                ->update([
                    'chiller_code' => $request->chiller_code,
                    'receiver_total_pieces' => $request->f_no_of_pieces,
                    'receiver_total_weight' => $request->net,
                    'received_by' => Auth::id(),
                    'with_variance' => $request->valid_match,
                    'updated_at' => now(),
                ]);

                $data = [
                    'product_code' => $transfer->product_code,
                    'transfer_from_location' => $transfer->transfer_from,
                    'transfer_to_location' => $transfer->location_code,
                    'receiver_total_pieces' => $request->pieces ?? 0,
                    'receiver_total_weight' => $request->weight,
                    'received_by' => Auth::id(),
                    'production_date' => $transfer->production_date,
                    'with_variance' => $request->valid_match,
                    'id' => $request->item_id,
                    'timestamp' => now()->toDateTimeString()
                ];
    
                // Publish data to RabbitMQ
                $helpers->publishToQueue($data, 'production_data_transfer.bc');

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
            ->whereIn('idt_transfers.location_code', ['3535', '3600'])
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
            ->whereIn('idt_transfers.location_code', ['3535', '3600'])
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
            ->whereIn('idt_transfers.location_code', ['3535', '3600'])
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
            ->whereIn('idt_transfers.location_code', ['3535', '3600'])
            ->whereDate('idt_transfers.created_at', today())
            ->groupBy('idt_transfers.product_code', 'items.description', 'idt_transfers.location_code')
            ->get();

        return view('despatch.idt-per-chiller', compact('title', 'items'));
    }

    public function takeStocks()
    {
        $title = 'Despatch Stocks';

        $items = DB::table('items')
            ->select('code', 'description', 'unit_of_measure', 'qty_per_unit_of_measure')
            ->get();

        $chillers = DB::table('chillers')
            ->where('location_code', '3535')
            ->get();

        $data = DB::table('stocks')
            ->join('items', 'stocks.product_code', '=', 'items.code')
            ->where('stocks.created_at', '>=', today()->subDays(10))
            ->select('stocks.*', 'items.description')
            ->get();

        return view('despatch.stocks_take', compact('title', 'items', 'chillers', 'data'));
    }

    public function saveStocks(Request $request, Helpers $helpers)
    {
        // dd($request->all());
        $parsedDate = Carbon::createFromFormat('d/m/Y', $request->prod_date)->toDateString();

        try {
            // try update
            DB::table('stocks')->insert([
                'product_code' => substr($request->product, 0, strpos($request->product, '-')),
                'weight' => $request->reading,
                'pieces' => $request->pieces,
                'location_code' => '3535',
                'chiller_code' => $request->chiller_code,
                'stock_date' => $parsedDate,
            ]);

            Toastr::success('Saved stocks successfully', 'Success');
            return redirect()
                ->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            Log::error('Exception in ' . __METHOD__ . '(): ' . $e->getMessage());
            return back();
        }
    }

    public function importStocks(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:csv,xlsx,xls|max:4096',
            ]);

            if ($validator->passes()) {
                Excel::import(new ImportStocks, $request->file('file')->store('temp'));

                Toastr::success('Stocks file imported successfully', 'Success');
                return redirect()
                    ->back();
            } else {
                $errors = $validator->errors();

                foreach ($errors->all() as $error) {
                    Toastr::error($error, 'Error!');
                }
                return back();
            }
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            Log::error('Exception in ' . __METHOD__ . '(): ' . $e->getMessage());
            return back();
        }
    }

    public function issueIdt($filter = null)
    {
        $title = "Send IDT from Despatch";

        $configs = DB::table('scale_configs')
                ->where('section', 'despatch')
                ->where('scale', 'Despatch Issue 2')
                ->get();

        $products = DB::table('products')->where('code', 'G1338')->get();

        $chillers = DB::table('chillers')->where('location_code', '1570')->get();

        $username = Auth::user()->username;

        $query = DB::table('idt_transfers')
            ->leftJoin('products', 'idt_transfers.product_code', '=', 'products.code')
            ->leftJoin('users', 'idt_transfers.user_id', '=', 'users.id')
            ->select('idt_transfers.*', 'products.description as product', 'products.unit_of_measure', 'users.username')
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->where('idt_transfers.transfer_from', '3535')
            ->where('idt_transfers.total_weight', '>', '0.0') // not cancelled
            ->whereDate('idt_transfers.created_at', '>=', today()->subDays(in_array(strtolower($username), array_map('strtolower', config('app.despatch_supervisors'))) ? 20 : 2)) // 20 days for supervisors, others 2 days back only.
            ->when($filter == 'butchery', function ($q) {
                $q->where('idt_transfers.location_code', '=', '1570'); // where transfer to location code is butchery
            });

        $transfer_lines = $query->get();

        return view('despatch.issue-idt', compact('title', 'transfer_lines', 'products', 'chillers', 'configs', 'filter'));
    }

    public function saveIssuedIdt(Request $request) {
        try {

            DB::table('idt_transfers')->insert([
                'product_code' => $request->product_code,
                'location_code' => $request->location_code,
                'chiller_code' => $request->chiller_code,
                'total_pieces' => $request->no_of_pieces ?: 0,
                'total_weight' => $request->net,
                'total_crates' => $request->total_crates ?: 0,
                'black_crates' => $request->black_crates,
                'full_crates' => $request->total_crates ?: 0,
                'incomplete_crate_pieces' => 0,
                'transfer_type' => 0,
                'transfer_from' => '3535',
                'description' => $request->description,
                'batch_no' => $request->batch_no,
                'user_id' => Auth::id(),
            ]);

            Toastr::success('Transfer saved successfully', 'Success');
            return redirect()->back();

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            Toastr::error($e->getMessage(), 'Error!');
            return redirect()->back();
        }
    }
}
