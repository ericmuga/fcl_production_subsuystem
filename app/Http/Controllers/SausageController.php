<?php

namespace App\Http\Controllers;

use App\Exports\SausageEntriesExport;
use App\Models\Helpers;
use App\Models\SausageEntry;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Faker\Core\Barcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Mockery\Matcher\Type;

class SausageController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check')->except(['insertBarcodes', 'lastInsert']);
    }

    public function index()
    {
        $title = "Dashboard";

        $total_tonnage = DB::table('sausage_entries')
            ->whereDate('sausage_entries.created_at', today())
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->sum(DB::raw('1 * items.qty_per_unit_of_measure'));

        $total_entries =  DB::table('sausage_entries')
            ->whereDate('sausage_entries.created_at', today())
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->count('sausage_entries.barcode');

        $highest_product = DB::table('sausage_entries')
            ->whereDate('sausage_entries.created_at', today())
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->where('items.code', '!=', null)
            ->select('sausage_entries.barcode', 'items.code', 'items.description', DB::raw('COUNT(sausage_entries.barcode) as total_count'), 'items.qty_per_unit_of_measure')
            ->groupBy('sausage_entries.barcode', 'items.code', 'items.description', 'items.qty_per_unit_of_measure')
            ->orderBy('total_count', 'DESC')
            ->limit(1)
            ->get()->toArray();

        $lowest_product = DB::table('sausage_entries')
            ->whereDate('sausage_entries.created_at', today())
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->where('items.code', '!=', null)
            ->select('sausage_entries.barcode', 'items.code', 'items.description', DB::raw('COUNT(sausage_entries.barcode) as total_count'), 'items.qty_per_unit_of_measure')
            ->groupBy('sausage_entries.barcode', 'items.code', 'items.description', 'items.qty_per_unit_of_measure')
            ->orderBy('total_count', 'ASC')
            ->limit(1)
            ->get()->toArray();

        $wrong_entries =  DB::table('sausage_entries')
            ->whereDate('sausage_entries.created_at', today())
            ->where('items.code', null)
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->count('sausage_entries.barcode');

        $transfers = DB::table('idt_transfers')
            ->whereDate('idt_transfers.created_at', today())
            ->whereIn('idt_transfers.transfer_from', ['2055', '1570'])
            ->select(
                DB::raw('SUM(CASE WHEN idt_transfers.transfer_from = 2055 THEN idt_transfers.total_pieces ELSE 0 END) as total_pieces_2055'),
                DB::raw('SUM(CASE WHEN idt_transfers.transfer_from = 2055 THEN idt_transfers.total_weight ELSE 0 END) as total_weight_2055'),
                DB::raw('SUM(CASE WHEN idt_transfers.transfer_from = 2055 THEN idt_transfers.receiver_total_pieces ELSE 0 END) as received_pieces_2055'),
                DB::raw('SUM(CASE WHEN idt_transfers.transfer_from = 2055 THEN idt_transfers.receiver_total_weight ELSE 0 END) as received_weight_2055'),

                DB::raw('SUM(CASE WHEN idt_transfers.transfer_from = 1570 THEN idt_transfers.total_pieces ELSE 0 END) as total_pieces_1570'),
                DB::raw('SUM(CASE WHEN idt_transfers.transfer_from = 1570 THEN idt_transfers.total_weight ELSE 0 END) as total_weight_1570'),
                DB::raw('SUM(CASE WHEN idt_transfers.transfer_from = 1570 THEN idt_transfers.receiver_total_pieces ELSE 0 END) as received_pieces_1570'),
                DB::raw('SUM(CASE WHEN idt_transfers.transfer_from = 1570 THEN idt_transfers.receiver_total_weight ELSE 0 END) as received_weight_1570')
            )
            ->first();

        return view('sausage.dashboard', compact('title', 'total_tonnage', 'total_entries', 'highest_product', 'lowest_product', 'wrong_entries', 'transfers'));
    }

    public function productionEntries($filter = null)
    {
        $title = "Todays-Entries";

        if (!$filter) {
            # no filter
            $entries = DB::table('sausage_entries')
                ->whereDate('sausage_entries.created_at', today())
                ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
                ->select('sausage_entries.barcode', 'items.code', 'items.description', DB::raw('COUNT(sausage_entries.barcode) as total_count'), 'items.qty_per_unit_of_measure')
                ->groupBy('sausage_entries.barcode', 'items.code', 'items.description', 'items.qty_per_unit_of_measure')
                ->orderBy('total_count', 'DESC')
                ->get();
        } elseif ($filter == 'highest-product') {
            $entries = DB::table('sausage_entries')
                ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
                ->whereDate('sausage_entries.created_at', today())
                ->where('items.code', '!=', null)
                ->select('sausage_entries.barcode', 'items.code', 'items.description', DB::raw('COUNT(sausage_entries.barcode) as total_count'), 'items.qty_per_unit_of_measure')
                ->groupBy('sausage_entries.barcode', 'items.code', 'items.description', 'items.qty_per_unit_of_measure')
                ->orderBy('total_count', 'DESC')
                ->limit(1)
                ->get();
        } elseif ($filter == 'lowest-product') {
            $entries = DB::table('sausage_entries')
                ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
                ->whereDate('sausage_entries.created_at', today())
                ->where('items.code', '!=', null)
                ->select('sausage_entries.barcode', 'items.code', 'items.description', DB::raw('COUNT(sausage_entries.barcode) as total_count'), 'items.qty_per_unit_of_measure')
                ->groupBy('sausage_entries.barcode', 'items.code', 'items.description', 'items.qty_per_unit_of_measure')
                ->orderBy('total_count', 'ASC')
                ->limit(1)
                ->get();
        } elseif ($filter == 'probable-wrong-entries') {
            $entries = DB::table('sausage_entries')
                ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
                ->whereDate('sausage_entries.created_at', today())
                ->where('items.code', null)
                ->select('sausage_entries.barcode', 'items.code', 'items.description', DB::raw('COUNT(sausage_entries.barcode) as total_count'), 'items.qty_per_unit_of_measure')
                ->groupBy('sausage_entries.barcode', 'items.code', 'items.description', 'items.qty_per_unit_of_measure')
                ->orderBy('total_count', 'DESC')
                ->get();
        }

        return view('sausage.entries', compact('entries', 'title', 'filter'));
    }

    public function exportSausageEntries(Request $request)
    {
        $from_date = Carbon::parse($request->from_date);
        $to_date = Carbon::parse($request->to_date);

        $entries = DB::table('sausage_entries')
            ->whereDate('sausage_entries.created_at', '>=', $from_date)
            ->whereDate('sausage_entries.created_at', '<=', $to_date)
            ->leftJoin('items', 'sausage_entries.barcode', '=', 'items.barcode')
            ->select('sausage_entries.barcode', 'items.code', 'items.description', DB::raw('COUNT(sausage_entries.barcode) as total_count'), 'items.qty_per_unit_of_measure', DB::raw('COUNT(sausage_entries.barcode) * items.qty_per_unit_of_measure  as total_tonnage'))
            ->groupBy('sausage_entries.barcode', 'items.code', 'items.description', 'items.qty_per_unit_of_measure')
            ->orderBy('total_count', 'DESC')
            ->get();

        $exports = Session::put('session_export_data', $entries);

        return Excel::download(new SausageEntriesExport, 'SausageScannersEntriesHistoryFor-' . $request->from_date . ' to ' . $request->to_date . '.xlsx');
    }

    public function itemsList()
    {
        $title = "Items-List";

        $items = Cache::remember('items_list', now()->addHours(10), function () {
            return DB::table('items')
                ->get();
        });

        return view('sausage.items', compact('title', 'items'));
    }

    public function lastInsert()
    {
        $res = '';

        $last = DB::table('sausage_entries')
            ->whereDate('created_at', today())
            ->select('origin_timestamp', 'scanner_ip', 'barcode')
            ->orderByDesc('id')
            ->limit(1)
            ->get()->toArray();

        if (!empty($last)) {
            $origin = $last[0]->origin_timestamp;
            $scanner = $last[0]->scanner_ip;
            $barcode = $last[0]->barcode;

            $res = $origin . ' ' . $scanner . ' ' . $barcode;
        }
        return response($res);
    }

    public function insertBarcodes(Request $request)
    {
        try {
            //saving...
            foreach ($request->request_data as $el) {
                // foreach (array_column($request->request_data, 500) as $el) {
                $el2 = explode(" ", $el);

                $entries = SausageEntry::upsert([
                    [
                        'origin_timestamp' => $el2[0],
                        'scanner_ip' => $el2[1],
                        'barcode' => $el2[2],
                    ],
                ], ['origin_timestamp', 'scanner_ip', 'barcode'], ['occurrences' => DB::raw('occurrences+1')]);
            }

            return response()->json([
                'success' => true,
                'message' => 'action successful'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    public function getIdt(Helpers $helpers)
    {
        $title = "IDT";

        $filter = '';

        $items = Cache::remember('items_list_sausage', now()->addHours(10), function () {
            return DB::table('items')
                ->where('blocked', '!=', 1)
                ->select('code', 'barcode', 'description', 'qty_per_unit_of_measure', 'unit_count_per_crate')
                ->get();
        });

        $transfer_lines = DB::table('idt_transfers')
            ->where('idt_transfers.transfer_from', '2055')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->leftJoin('users', 'idt_transfers.received_by', '=', 'users.id')
            ->select('idt_transfers.*', 'items.description as product', 'items.qty_per_unit_of_measure', 'items.unit_count_per_crate', 'users.username')
            ->whereDate('idt_transfers.created_at', today())
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->get();

        return view('sausage.idt', compact('title', 'filter', 'transfer_lines', 'items', 'helpers'));
    }

    public function getItemDetails(Request $request)
    {
        $item = DB::table('items')
            ->where('code', $request->product_code)
            ->select('qty_per_unit_of_measure', 'unit_count_per_crate')
            ->first();

        return response()->json($item);
    }

    public function getTransferToLocations(Request $request)
    {
        $data = DB::table('chillers')
            ->leftJoin('item_location_combinations', 'item_location_combinations.location', '=', 'chillers.location_code')
            ->where('item_location_combinations.item_code', $request->product_code)
            ->select('chillers.chiller_code', 'chillers.location_code', 'chillers.description')
            ->get();

        return response()->json($data);
    }

    public function validateUser(Request $request, Helpers $helpers)
    {
        $request_data = [
            "username" => $request->username,
            "password" => $request->password,
        ];

        // return response()->json($request->all());

        $post_data = json_encode($request_data);

        $result = $helpers->validateLogin($post_data);

        return response()->json($result);
    }

    public function checkUserRights(Request $request)
    {
        $status = 0;

        $result = DB::table('transfer_user_rights')
            ->where('username', $request->username)
            ->where('location_code', $request->location_code)
            ->first();

        if ($result != null) {
            #exists
            $status = 1;
        }

        return response()->json($status);
    }

    private function getLocationCode($export_status, $location_code)
    {
        $location = $location_code;

        if ($export_status == 1) {
            $location = 3600;
        }
        return $location;
    }

    public function saveTransfer(Request $request, Helpers $helpers)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'crates_valid' => 'required|boolean',
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
            // try save
            DB::table('idt_transfers')->insert([
                'product_code' => $request->product,
                'location_code' => $this->getLocationCode($request->for_export, $request->location_code),
                'chiller_code' => $request->chiller_code,
                'total_crates' => $request->total_crates,
                'full_crates' => $request->full_crates,
                'incomplete_crate_pieces' => $request->incomplete_pieces,
                'total_pieces' => $request->pieces,
                'total_weight' => $request->weight,
                'transfer_type' => $request->for_export,
                'transfer_from' => '2055',
                'description' => $request->desc,
                'order_no' => $request->order_no,
                'batch_no' => $request->batch . $request->batch_no,
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

    public function editIdtIssue(Request $request, Helpers $helpers)
    {
        try {
            $location_code = '3535';

            if ($request->for_export_edit == 1) {
                # export...
                $location_code = '3600';
            }

            DB::transaction(function () use ($request, $helpers, $location_code) {
                //update idt issue
                DB::table('idt_transfers')->where('id', $request->item_id)
                    ->update([
                        'description' => $request->product,
                        'transfer_type' => $request->for_export_edit,
                        'location_code' => $location_code,
                        'batch_no' => $request->batch . $request->batch_no_edit,
                        'total_pieces' => (int)$request->pieces_edit,
                        'total_weight' => $request->weight_edit,
                        'edited' => 1,
                    ]);

                //insert change logs
                DB::table('idt_changelogs')->insert([
                    'table_name' => 'idt_transfers',
                    'item_id' => $request->item_id,
                    'changed_by' => $helpers->authenticatedUserId(),
                    'total_pieces' => (int)$request->pieces_edit,
                    'total_weight' => $request->weight_edit,
                    'previous_pieces' => (int)$request->old_pieces,
                    'previous_weight' => $request->old_weight,
                ]);
            });

            Toastr::success('IDT Transfer Updated successfully', 'Success');
            return redirect()
                ->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back();
        }
    }

    public function idtReport(Helpers $helpers, $filter = null)
    {
        $title = "IDT-Report";

        $items = Cache::remember('items_list', now()->addHours(10), function () {
            return DB::table('items')
                ->select('code', 'barcode', 'description', 'qty_per_unit_of_measure', 'unit_count_per_crate')
                ->get();
        });

        $transfer_lines = DB::table('idt_transfers')
            ->whereIn('idt_transfers.transfer_from', ['1570', '2055'])
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->leftJoin('products', 'idt_transfers.product_code', '=', 'products.code')
            ->leftJoin('users', 'idt_transfers.received_by', '=', 'users.id')
            ->select('idt_transfers.*', 'items.description as product', 'products.description as product2', 'items.qty_per_unit_of_measure', 'items.unit_count_per_crate', 'users.username')
            ->when($filter == 'today', function ($q) {
                $q->whereDate('idt_transfers.created_at', today()); // today only
            })
            ->when($filter == 'history', function ($q) {
                $q->whereDate('idt_transfers.created_at', '>=', today()->subDays(7)); // today plus last 7 days
            })
            ->orderByDesc('idt_transfers.id')
            ->get();

        return view('sausage.idt-report', compact('title', 'filter', 'transfer_lines', 'items', 'helpers'));
    }

    public function getReceiveIdt(Helpers $helpers, $filter = null)
    {
        $title = "IDT-Receive";

        $configs = Cache::remember('sausage_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('section', 'sausage')
                ->where('scale', 'Sausage')
                ->select('scale', 'tareweight', 'comport')
                ->get()->toArray();
        });

        $transfer_lines = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->leftJoin('products', 'idt_transfers.product_code', '=', 'products.code')
            ->leftJoin('users', 'idt_transfers.user_id', '=', 'users.id')
            ->select('idt_transfers.*', 'items.description as product', 'products.description as product2', 'items.qty_per_unit_of_measure', 'items.unit_count_per_crate', 'users.username')
            ->whereDate('idt_transfers.created_at', '>=', today()->subDays(2))
            ->where('idt_transfers.transfer_from', '1570')
            ->where('idt_transfers.location_code', '2055') // sausage
            ->where('idt_transfers.received_by', '=', null)
            ->where('idt_transfers.total_weight', '>', '0.0') // not cancelled
            ->orderByDesc('idt_transfers.id')
            ->get();

        return view('sausage.idt-receive', compact('title', 'transfer_lines', 'configs', 'helpers'));
    }

    public function updateReceiveIdt(Request $request, Helpers $helpers)
    {
        // dd($request->all());
        try {
            // try update
            DB::table('idt_transfers')
                ->where('id', $request->item_id)
                ->update([
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
            $helpers->CustomErrorlogger($e);
            return back()
                ->withInput();
        }
    }

    public function getBatchNoAxios(Request $request, Helpers $helpers)
    {
        $data = $helpers->generateIdtBatch($request->production_date);

        return response()->json($data);
    }

    public function perBatchReport($filter = null)
    {
        $title = 'Batches Report';

        $per_batch = DB::table('idt_transfers')
            ->where('idt_transfers.transfer_from', '2055')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->select('idt_transfers.batch_no', DB::raw('SUM(idt_transfers.total_pieces) AS pieces'), DB::raw('SUM(idt_transfers.total_weight) as weight'))
            ->groupBy('idt_transfers.batch_no')
            ->when($filter == null, function ($q) {
                $q->whereDate('idt_transfers.created_at', today()); // today
            })
            ->orderBy('idt_transfers.batch_no', 'DESC')
            ->get();

        return view('sausage.per-batch-report', compact('title', 'per_batch', 'filter'));
    }
}
