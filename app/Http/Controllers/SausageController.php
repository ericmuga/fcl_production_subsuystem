<?php

namespace App\Http\Controllers;

use App\Exports\SausageEntriesExport;
use App\Models\Helpers;
use App\Models\SausageEntry;
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

class SausageController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['insertBarcodes', 'lastInsert']);
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

    public function getIdt(Helpers $helpers, $filter = null)
    {
        // dd($filter);
        if($filter == 'highcare') {
            return redirect()->route('list_issued_idt', ['from_location' => '2055', 'to_location' => '2500']);
        } 
        
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
        $data = Cache::remember('chillers', now()->addHours(12), function () {
            return DB::table('chillers')
                ->select('chillers.chiller_code', 'chillers.location_code', 'chillers.description')
                ->get();
        });

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
        } elseif ($export_status == 3035) {
            $location = 3035;
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
                'user_id' => Auth::id(),
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
                    'changed_by' => Auth::id(),
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
            ->leftJoin('template_lines', function ($join) {
                $join->on('idt_transfers.product_code', '=', 'template_lines.item_code')
                     ->where('template_lines.type', '=', 'Output');
            })
            ->leftJoin('products', 'idt_transfers.product_code', '=', 'products.code')
            ->leftJoin('users', 'idt_transfers.received_by', '=', 'users.id')
            ->select('idt_transfers.*', 'template_lines.description as template_output','items.description as product', 'products.description as product2', 'items.qty_per_unit_of_measure', 'items.unit_count_per_crate', 'users.username')
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
        $transfer = DB::table('idt_transfers')
            ->where('id', $request->item_id)
            ->first();

        try {
            // try update
            DB::table('idt_transfers')
                ->where('id', $request->item_id)
                ->update([
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
                'receiver_total_pieces' => $request->f_no_of_pieces ?? 0,
                'receiver_total_weight' => $request->net,
                'received_by' => Auth::id(),
                'production_date' => $transfer->production_date,
                'with_variance' => $request->valid_match,
                'timestamp' => now()->toDateTimeString(),
                'id' => $request->item_id
            ];

            // Publish data to RabbitMQ
            //$helpers->publishToQueue($data, 'production_data_transfer.bc');

            Toastr::success('IDT Transfer received successfully', 'Success');
            return redirect()
                ->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            $helpers->CustomErrorlogger($e->getMessage(),  __FUNCTION__);
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

    public function stuffingWeights(Helpers $helpers)
    {
        $title = 'Stuffing weights';

        $items = $this->stuffingItems();

        $itemCodes = $items->pluck('item_code')->toArray();

        // Packed (Packing process) items each stuffing item ends up as, keyed by the
        // stuffing item. Each option carries the recipe path that leads to it.
        $recipe_outputs = $this->cachedPackingRoutes();

        $configs = Cache::remember('stuffing_weigh_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('section', 'stuffing')
                ->first();
        });

        $stuffing_transfers = DB::table('idt_transfers')
            ->select('idt_transfers.*', 'users.username')
            ->whereIn('idt_transfers.product_code', $itemCodes)
            ->leftJoin('users', 'users.id', '=', 'idt_transfers.received_by')
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->whereDate('idt_transfers.created_at', '>=', today()->subDays(2))
            ->get();

        $generated_orders = DB::table('generated_production_orders')
            ->leftJoin('users', 'users.id', '=', 'generated_production_orders.user_id')
            ->select('generated_production_orders.*', 'users.username')
            ->whereDate('generated_production_orders.created_at', '>=', today()->subDays(2))
            ->orderByDesc('generated_production_orders.id')
            ->get()
            ->groupBy('production_order_no');

        return view('sausage.stuffing', compact('title','items', 'configs', 'stuffing_transfers', 'recipe_outputs', 'generated_orders', 'helpers'));
    }

    private function stuffingItems()
    {
        return Cache::remember('stuffing_products', now()->addHours(10), function () {
            $items = DB::table('template_lines')
                ->where('type', 'Output')
                ->where('description', 'like', 'mix for%')
                ->select('item_code', 'description')
                ->get();

            $special_product = DB::table('products')
                ->where('code', 'G4470')
                ->select('code as item_code', 'description')
                ->first();

            if ($special_product) {
                $items->push($special_product);
            }

            return $items;
        });
    }

    private function cachedPackingRoutes()
    {
        $itemCodes = $this->stuffingItems()->pluck('item_code')->toArray();

        return Cache::remember('stuffing_packing_outputs', now()->addHours(10), function () use ($itemCodes) {
            return $this->packingRoutesFor($itemCodes);
        });
    }

    /**
     * Walk the recipe graph forward from each given item until the Packing step and
     * return the packed items reachable from it, each with the route taken to get
     * there. Most items reach Packing in two steps (stuff, then pack); the ones that
     * are smoked take three.
     */
    private function packingRoutesFor(array $itemCodes, int $maxDepth = 3)
    {
        $edges = DB::table('RecipeData')
            ->whereNotNull('input_item')
            ->whereNotNull('output_item')
            ->select(
                'process', 'recipe', 'input_item', 'output_item',
                'input_item_qt_per', 'output_item_uom', 'output_item_location'
            )
            ->get()
            ->groupBy('input_item');

        $routes = [];

        foreach (array_unique($itemCodes) as $code) {
            $found = [];
            $queue = [[$code, []]];

            while ($queue) {
                [$item, $path] = array_shift($queue);

                if (count($path) >= $maxDepth) {
                    continue;
                }

                foreach ($edges[$item] ?? [] as $edge) {
                    // Guard against a recipe that loops back on itself
                    if ($edge->output_item === $item) {
                        continue;
                    }

                    $route = array_merge($path, [$edge]);

                    // Packing is the end of the line - don't walk past it
                    if ($edge->process === 'Packing') {
                        if (!isset($found[$edge->output_item]) || count($route) < count($found[$edge->output_item])) {
                            $found[$edge->output_item] = $route;
                        }

                        continue;
                    }

                    $queue[] = [$edge->output_item, $route];
                }
            }

            if ($found) {
                $routes[$code] = $found;
            }
        }

        $packedCodes = collect($routes)->flatMap(fn($found) => array_keys($found))->unique()->values()->toArray();

        // output_item_dec is blank or the literal string 'NULL' on many rows, so take
        // it from wherever it is populated and fall back to the item master.
        $descriptions = DB::table('RecipeData')
            ->whereIn('output_item', $packedCodes)
            ->whereNotNull('output_item_dec')
            ->whereNotIn('output_item_dec', ['', 'NULL'])
            ->pluck('output_item_dec', 'output_item');

        $fallbacks = DB::table('items')->whereIn('code', $packedCodes)->pluck('description', 'code')
            ->merge(DB::table('products')->whereIn('code', $packedCodes)->pluck('description', 'code'));

        return collect($routes)->map(function ($found) use ($descriptions, $fallbacks) {
            return collect($found)
                ->map(fn($route, $packedCode) => (object) [
                    'output_item' => $packedCode,
                    'output_item_dec' => $descriptions[$packedCode] ?? $fallbacks[$packedCode] ?? null,
                    'steps' => count($route),
                    'route' => array_values($route),
                ])
                ->sortBy('output_item')
                ->values();
        });
    }

    /**
     * BC keys the production order prefix off the routing, so every step of one
     * chain shares a number and differs only in this prefix - which is what the
     * existing script relies on when it turns a P20 packing order into its P19
     * stuffing counterpart.
     */
    const ROUTING_PREFIXES = [
        'Stuffing-2055'     => 'P19',
        'Filling-2595'      => 'P19',
        'Packing-1570'      => 'P15',
        'Packing-2055'      => 'P20',
        'Packing-2500'      => 'P21',
        'Packing-2595'      => 'P22',
        'Cont-Smoking'      => 'P35',
        'Mincing-1570-2055' => 'P16',
        'Slicing-1570'      => 'P08',
        'Curing-1570-2500'  => 'P37A',
    ];

    /**
     * Routing is normally "<process>-<location the input is drawn from>", but BC
     * names a few differently. RecipeData.routing is almost entirely null so it
     * cannot be used as the source.
     */
    const PROCESS_ROUTINGS = [
        'Smoking' => 'Cont-Smoking',
    ];

    /**
     * Items the existing stuffing script excludes from order generation.
     */
    const ORDER_GENERATION_EXCLUSIONS = [
        'G2206', 'G2005', 'G1468', 'G2267', 'G2279',
        'G2295', 'G2297', 'G2268', 'J31015806', 'G2210',
    ];

    private function routingFor(?string $process, ?string $location): string
    {
        return self::PROCESS_ROUTINGS[$process] ?? $process . '-' . $location;
    }

    /**
     * Build a production order number the way BC does: a routing-derived prefix,
     * the recipe with its 1210/1220/1230/1240 series compressed to 1/2/3/4, and
     * the id of the record the order came from - here the weighing.
     */
    private function productionOrderNo(string $routing, string $finalRecipe, int $transferId): string
    {
        $prefix = self::ROUTING_PREFIXES[$routing] ?? null;

        if (!$prefix) {
            Log::warning('No production order prefix mapped for routing', ['routing' => $routing]);
            $prefix = 'P00';
        }

        $series = str_replace(['1210', '1220', '1230', '1240'], ['1', '2', '3', '4'], $finalRecipe);

        return $prefix . '_' . $series . '_' . $transferId;
    }

    /**
     * Generate one production order per step between the item just weighed and the
     * packed item chosen on the form - two steps normally (stuff, pack), three when
     * the chain passes through smoking.
     *
     * Quantities follow the recipe yields: at each step the carried quantity is
     * divided by that item's input_item_qt_per to get a scale factor, which is then
     * applied to the recipe's batch_size for the output line and to every other
     * input's qt_per for the consumption lines.
     */
    private function generateProductionOrders(int $transferId, ?string $weighedItem, ?string $packedItem, float $netWeight, ?string $batchNo): array
    {
        if (!$weighedItem || !$packedItem || $netWeight <= 0) {
            return ['orders' => 0, 'message' => 'No packed item selected - no production orders generated.'];
        }

        if (in_array($weighedItem, self::ORDER_GENERATION_EXCLUSIONS) || in_array($packedItem, self::ORDER_GENERATION_EXCLUSIONS)) {
            return ['orders' => 0, 'message' => "{$weighedItem} is excluded from production order generation."];
        }

        if (DB::table('generated_production_orders')->where('idt_transfer_id', $transferId)->exists()) {
            return ['orders' => 0, 'message' => 'Production orders were already generated for this weighing.'];
        }

        $option = collect($this->cachedPackingRoutes()[$weighedItem] ?? [])
            ->firstWhere('output_item', $packedItem);

        if (!$option) {
            return ['orders' => 0, 'message' => "No recipe route from {$weighedItem} to {$packedItem} - no production orders generated."];
        }

        $route = $option->route;
        $finalRecipe = end($route)->recipe;

        $recipeLines = DB::table('RecipeData')
            ->whereIn('recipe', collect($route)->pluck('recipe')->unique()->toArray())
            ->select(
                'recipe', 'process', 'output_item', 'output_item_uom', 'output_item_location',
                'batch_size', 'input_item', 'input_item_uom', 'input_item_qt_per', 'input_item_location'
            )
            ->get()
            ->groupBy('recipe');

        $rows = [];
        $carriedItem = $weighedItem;
        $carriedQty = $netWeight;
        $now = now();

        foreach ($route as $index => $edge) {
            $step = $index + 1;

            $lines = collect($recipeLines[$edge->recipe] ?? [])
                ->where('output_item', $edge->output_item)
                ->unique('input_item')
                ->values();

            $anchor = $lines->firstWhere('input_item', $carriedItem);

            if (!$anchor || (float) $anchor->input_item_qt_per <= 0 || (float) $anchor->batch_size <= 0) {
                Log::warning('Stuffing production order generation stopped', [
                    'transfer_id' => $transferId,
                    'step' => $step,
                    'recipe' => $edge->recipe,
                    'carried_item' => $carriedItem,
                ]);

                return [
                    'orders' => 0,
                    'message' => "Recipe {$edge->recipe} has no usable quantity for {$carriedItem} - no production orders generated.",
                ];
            }

            $scale = $carriedQty / (float) $anchor->input_item_qt_per;
            $outputQty = (float) $anchor->batch_size * $scale;

            $routing = $this->routingFor($edge->process, $anchor->input_item_location);

            $common = [
                'production_order_no' => $this->productionOrderNo($routing, $finalRecipe, $transferId),
                'routing' => $routing,
                'process' => $edge->process,
                'recipe' => $edge->recipe,
                'step' => $step,
                'idt_transfer_id' => $transferId,
                'weighed_item' => $weighedItem,
                'packed_item' => $packedItem,
                'batch_no' => $batchNo,
                'net_weight' => $netWeight,
                'user_id' => Auth::id(),
                'transaction_date' => $now->toDateString(),
                'created_at' => $now,
                'updated_at' => $now,
            ];

            // Line 1000 is the produced item and carries its own recipe; the
            // consumption lines that follow carry the packing recipe the whole
            // chain is being produced for, as BC's orders do.
            $rows[] = $common + [
                'line_no' => 1000,
                'item_no' => $edge->output_item,
                'quantity' => round($outputQty, 5),
                'uom' => $anchor->output_item_uom,
                'location_code' => $anchor->output_item_location,
                'external_document_no' => $edge->recipe,
                'line_type' => 'output',
            ];

            $lineNo = 2000;

            foreach ($lines as $line) {
                $rows[] = $common + [
                    'line_no' => $lineNo,
                    'item_no' => $line->input_item,
                    'quantity' => round((float) $line->input_item_qt_per * $scale, 5),
                    'uom' => $line->input_item_uom,
                    'location_code' => $line->input_item_location,
                    'external_document_no' => $finalRecipe,
                    'line_type' => 'consumption',
                ];

                $lineNo += 1000;
            }

            $carriedItem = $edge->output_item;
            $carriedQty = $outputQty;
        }

        DB::table('generated_production_orders')->insert($rows);

        $pushed = $this->pushToProductionData($rows);

        $orders = count($route);

        return [
            'orders' => $orders,
            'message' => $orders . ' production order(s) generated, ' . count($rows) . ' lines' . $pushed . '.',
        ];
    }

    /**
     * Copy generated lines into the BC ProductionData table when the app is
     * configured to write there. Lines already present for the same order and
     * line number are skipped, mirroring the NOT EXISTS guard in the SQL script
     * this replaces.
     */
    private function pushToProductionData(array $rows): string
    {
        if (config('production_orders.target') !== 'production_data') {
            return '';
        }

        $table = config('production_orders.production_data_table', 'ProductionData');

        try {
            $orderNos = collect($rows)->pluck('production_order_no')->unique()->values()->toArray();

            $existing = DB::table($table)
                ->whereIn('ProductionOrderNo', $orderNos)
                ->select('ProductionOrderNo', 'LineNo')
                ->get()
                ->mapWithKeys(fn($r) => [$r->ProductionOrderNo . '|' . $r->LineNo => true]);

            $decimals = (int) config('production_orders.production_data_decimals', 2);
            $username = Auth::user()->username ?? 'WMS';

            $pending = [];

            foreach ($rows as $row) {
                if (isset($existing[$row['production_order_no'] . '|' . $row['line_no']])) {
                    continue;
                }

                // ID is an identity column and every remaining column is NOT NULL
                $pending[] = [
                    'ProductionOrderNo' => $row['production_order_no'],
                    'LineNo' => $row['line_no'],
                    'ItemNo' => $row['item_no'],
                    'Quantity' => round($row['quantity'], $decimals),
                    'UOM' => $row['uom'] ?: 'KG',
                    'LocationCode' => $row['location_code'] ?: '',
                    'BinCode' => '',
                    'UserName' => $username,
                    'Routing' => $row['routing'],
                    'DateTime' => $row['created_at'],
                    'Status' => 0,
                    'FinishedProductionOrderNo' => '',
                    'ExternalDocumentNo' => $row['external_document_no'],
                    'TransactionDate' => $row['transaction_date'],
                    'Published' => 0,
                ];
            }

            if (!$pending) {
                return ', already present in ' . $table;
            }

            DB::table($table)->insert($pending);

            return ', ' . count($pending) . ' written to ' . $table;

        } catch (\Exception $e) {
            // The weight and the local copy are already saved; surface the failure
            // rather than losing them.
            Log::error('Failed writing generated orders to ProductionData: ' . $e->getMessage());

            return ', but writing to ProductionData failed (' . $e->getMessage() . ')';
        }
    }

    public function saveStuffingWeights(Request $request, Helpers $helpers) {
        $manual_weight = 0;
        if ($request->manual_weight == 'on') {
            $manual_weight = 1;
        }

        try {
            $data = [
                'product_code' => $request->product_code,
                'location_code' => '',
                'total_weight' => $request->net_weight,
                'transfer_from' => '',
                'batch_no' => $request->batch_no,
                'description' => $request->output_item,
                'manual_weight' => $manual_weight,
                'user_id' => Auth::id(),
                'receiver_total_weight' => $request->net_weight,
                'received_by' => Auth::id(),
                'transfer_type' => 0,
            ];
            $transferId = DB::table('idt_transfers')->insertGetId($data);

            //write to rabbitmq
            $data['timestamp'] = now()->toDateTimeString();
            //$helpers->publishToQueue($data, 'stuffing_transfers.bc');

            // A gap in the recipe data must not cost the operator their weight, so the
            // orders are generated after the weight is safely saved and any failure is
            // reported rather than thrown.
            $generated = $this->generateProductionOrders(
                $transferId,
                $request->product_code,
                $request->output_item,
                (float) $request->net_weight,
                $request->batch_no
            );

            return response()->json([
                'success' => true,
                'message' => 'Stuffing weight saved successfully',
                'production_orders' => $generated['orders'],
                'production_orders_message' => $generated['message'],
            ]);

        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to save stuffing weight. Error: ' . $e->getMessage()]);
        }
    }
}
