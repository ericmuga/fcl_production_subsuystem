<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use App\Models\SausageEntry;
use Brian2694\Toastr\Facades\Toastr;
use Faker\Core\Barcode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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

        return view('sausage.dashboard', compact('title', 'total_tonnage', 'total_entries', 'highest_product', 'lowest_product', 'wrong_entries'));
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
        $last = DB::table('sausage_entries')
            ->whereDate('created_at', today())
            ->select('origin_timestamp', 'scanner_ip', 'barcode')
            ->orderByDesc('id')
            ->limit(1)
            ->get()->toArray();

        $res = '';
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
            // foreach ($request->request_data as $el) {
            foreach (array_column($request->request_data, 500) as $el) {
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

        $items = Cache::remember('items_list', now()->addHours(10), function () {
            return DB::table('items')
                ->select('code', 'barcode', 'description', 'qty_per_unit_of_measure', 'unit_count_per_crate')
                ->get();
        });

        $transfer_lines = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->leftJoin('users', 'idt_transfers.user_id', '=', 'users.id')
            ->select('idt_transfers.*', 'items.description', 'items.qty_per_unit_of_measure', 'items.unit_count_per_crate', 'users.username')
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

    public function saveIdTransfer(Request $request, Helpers $helpers)
    {
        // dd($request->all());
        $validator = Validator::make($request->all(), [
            'crates_valid' => 'required|boolean',
            'user_valid' => 'required|boolean',

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
                'location_code' => $request->location_code,
                'chiller_code' => $request->chiller_code,
                'total_crates' => $request->total_crates,
                'full_crates' => $request->full_crates,
                'incomplete_crate_pieces' => $request->incomplete_pieces,
                'total_pieces' => $request->pieces,
                'total_weight' => $request->weight,
                'received_by' => $request->username,
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
}
