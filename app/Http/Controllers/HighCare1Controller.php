<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HighCare1Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except([]);
    }

    public function index()
    {
        $title = "Todays-Entries";

        $transfers = DB::table('idt_transfers')
            ->whereDate('idt_transfers.created_at', today())
            ->whereIn('idt_transfers.transfer_from', ['2595', '2500'])
            ->select(DB::raw('SUM(idt_transfers.receiver_total_pieces) as received_pieces'), DB::raw('SUM(idt_transfers.receiver_total_weight) as received_weight'), DB::raw('SUM(idt_transfers.total_pieces) as issued_pieces'), DB::raw('SUM(idt_transfers.total_weight) as issued_weight'))
            ->groupBy('idt_transfers.transfer_from')
            ->get();

        return view('highcare1.dashboard', compact('title', 'transfers'));
    }

    public function getIdt(Helpers $helpers, $filter = null)
    {
        $title = "IDT";

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
            ->where('idt_transfers.transfer_from', $filter??'2595')
            ->where('idt_transfers.filter1', null)
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->get();

        return view('highcare1.idt', compact('title', 'items', 'transfer_lines', 'helpers', 'filter'));
    }

    public function getIdtBulk(Helpers $helpers)
    {
        $title = "IDT";

        $configs = Cache::remember('highcare1_configs', now()->addHours(12), function () {
            return DB::table('scale_configs')
                ->where('section', 'highcare1')
                ->where('scale', 'Highcare1')
                ->select('scale', 'tareweight', 'comport')
                ->get()->toArray();
        });

        $items = Cache::remember('items_list_sausage_bulk', now()->addHours(10), function () {
            $items = DB::table('items')
                ->where('blocked', '!=', 1)
                ->select('code', 'barcode', 'description', 'qty_per_unit_of_measure', 'unit_count_per_crate')
                ->get();

            $select_products = ['G4451','G4452','G4453','G4456','G4457','G4459','G4460','G4461','G4462','G4463','G4464','G4465','G4466','G4467','G4470'];
            $products = DB::table('products')
                ->whereIn('code', $select_products)
                ->select('id', 'code', DB::raw('code as barcode'), 'description', 'unit_of_measure')
                ->get();

            return $items->merge($products);
        });

        // dd($items);

        $tags = Cache::remember('tags', now()->addHours(10), function () {
            return DB::table('receipts')
                ->select('vendor_tag')
                ->where('vendor_tag', '!=', '')
                ->distinct()
                ->get();
        });

        $transfer_lines = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->leftJoin('users', 'idt_transfers.received_by', '=', 'users.id')
            ->select('idt_transfers.*', 'items.description as product', 'items.qty_per_unit_of_measure', 'items.unit_count_per_crate', 'users.username')
            ->whereDate('idt_transfers.created_at', today())
            ->where(function ($query) {
                $query->where('idt_transfers.transfer_from', '2595')
                    ->orWhere('idt_transfers.transfer_from', '2500');
            })
            ->where('idt_transfers.filter1', 'bulk')
            ->orderByDesc('idt_transfers.id')
            ->get();

        return view('highcare1.idt-bulk', compact('title', 'items', 'transfer_lines', 'configs', 'helpers', 'tags'));
    }

    public function saveIdtBulk(Request $request, Helpers $helpers)
    {
        $location = $request->transfer_to;
        if($request->transfer_type == '2' && $request->transfer_to == '3535'){
            $location = '3600';
        } 

        $tranfer_from_2595 = ['J31022101', 'J31050701', 'J31022210', 'J31022211', 'J31050905', 'J31090264', 'J31020851', 'J31022751', 'J31022551', 'J31090176', 'J31022851', 'J31020620', 'J31020621', 'J31020622', 'G4470']; //from location 2595

        $transfer_from = '2500'; //bacon and ham, default

        if (in_array($request->product, $tranfer_from_2595)) {
            $transfer_from = '2595'; //highcare
        }

        $off_cuts = ['G4451','G4452','G4453','G4456','G4457','G4459','G4460','G4461','G4462','G4463','G4464','G4465','G4466','G4467'];
        
        if (in_array($request->product, $off_cuts)) {
            $transfer_from = '2595'; //off cuts from highcare
        }

        try {
            // try save
            DB::table('idt_transfers')->insert([
                'product_code' => $request->product,
                'location_code' => $location, //transfer to dispatch default
                'chiller_code' => $request->chiller_code,
                'total_pieces' => $request->no_of_pieces ?: 0,
                'total_weight' => $request->net,
                'black_crates' => $request->black_crates,
                'total_crates' => $request->no_of_crates ?: 0,
                'full_crates' => $request->no_of_crates ?: 0,
                'incomplete_crate_pieces' => 0,
                'transfer_type' => $request->transfer_type,
                'transfer_from' => $transfer_from,
                'description' => $request->desc,
                'order_no' => $request->order_no,
                'batch_no' => $request->batch_no,
                'description' => $request->desc_all,
                'user_id' => Auth::id(),
                'filter1' => 'bulk',
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
        $title = "IDT-Report";

        $transfer_lines = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->leftJoin('users', 'idt_transfers.received_by', '=', 'users.id')
            ->select('idt_transfers.*', 'items.description as product', 'items.qty_per_unit_of_measure', 'items.unit_count_per_crate', 'users.username')
            ->when($filter == 'today', function ($q) {
                $q->whereDate('idt_transfers.created_at', today()); // today only
            })
            ->when($filter == '', function ($q) {
                $q->whereDate('idt_transfers.created_at', '>=', today()->subDays(7)); // today plus last 7 days
            })
            ->where('idt_transfers.transfer_from', '=', '2595')
            ->orWhere('idt_transfers.transfer_from', '=', '2500')
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->get();

        return view('highcare1.idt-report', compact('title', 'filter', 'transfer_lines', 'helpers'));
    }

    public function saveTransfer(Request $request, Helpers $helpers)
    {
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
                'location_code' => $helpers->getLocationCode($request->for_export, $request->location_code),
                'chiller_code' => $request->chiller_code,
                'total_crates' => $request->total_crates,
                'full_crates' => $request->full_crates,
                'incomplete_crate_pieces' => $request->incomplete_pieces,
                'total_pieces' => $request->pieces,
                'total_weight' => $request->weight,
                'transfer_type' => $request->for_export,
                'transfer_from' => $request->transfer_from,
                'description' => $request->desc,
                'order_no' => $request->order_no,
                'batch_no' => $request->batch_no,
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

    public function getReceiveIdt(Helpers $helpers, $filter = null)
    {
        $title = "IDT-Receive";

        $configs = Cache::remember('highcare1_configs', now()->addHours(12), function () {
            return DB::table('scale_configs')
                ->where('section', 'highcare1')
                ->where('scale', 'Highcare1')
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
            ->where('idt_transfers.location_code', '2500') // curing
            ->where('idt_transfers.received_by', '=', null)
            ->where('idt_transfers.total_weight', '>', '0.0') // not cancelled
            ->orderByDesc('idt_transfers.id')
            ->get();

        return view('highcare1.idt-receive', compact('title', 'transfer_lines', 'configs', 'helpers'));
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

    public function getBaconSlicing(Helpers $helpers, $filter = null)
    {
        $title = "Bacon Production";

        $configs = DB::table('scale_configs')
            ->where('scale', 'BaconSlicing')
            ->select('scale', 'tareweight', 'comport')
            ->get()->toArray();

        $bacon_products = ['G3813', 'G4501', 'G3810', 'G4536', 'G3814', 'G4547', 'G3812', 'G4544', 'G3804', 'G4503', 'G3813X', 'G4501X', 'G3591', 'G4525', 'G3802', 'G4452', 'G4540', 'G3309', 'G4522', 'G38101'];

        $products = Cache::remember('bacon_products', now()->addMinutes(480), function () use ($bacon_products) {
            return DB::table('products')
                ->whereIn('products.code', $bacon_products) //bacon slicing items only 
                ->join('product_processes', 'product_processes.product_code', '=', 'products.code')
                ->join('processes', 'product_processes.process_code', '=', 'processes.process_code')
                ->join('product_types', 'product_processes.product_type', '=', 'product_types.code')
                ->select(DB::raw('TRIM(products.code) as code'), 'products.description', 'product_types.description as product_type_name', 'product_types.code as product_type_code', 'product_processes.process_code', 'processes.process', 'processes.shortcode')
                ->get();
        });

        $bacon_data = DB::table('bacon_slicing')
            ->where('processes.process_code', '!=', '15')
            ->leftJoin('product_types', 'bacon_slicing.product_type', '=', 'product_types.code')
            ->leftJoin('processes', 'bacon_slicing.process_code', '=', 'processes.process_code')
            ->leftJoin('products', 'bacon_slicing.item_code', '=', 'products.code')
            ->select('bacon_slicing.*', 'product_types.code AS type_id', 'product_types.description AS product_type', 'processes.process', 'processes.process_code', 'products.description')
            ->orderBy('bacon_slicing.created_at', 'DESC')
            ->when($filter == 'admin', function ($q) {
                $q->whereBetween(
                    'bacon_slicing.created_at',
                    [now()->startOfWeek(), now()->endOfWeek()]
                ); // today plus last 7 days
            })
            ->when($filter != 'admin', function ($q) {
                $q->whereDate('bacon_slicing.created_at', today()); // today only
            })
            ->get();

        // dd($products);

        return view('highcare1.bacon_slicing', compact('title', 'filter', 'bacon_data', 'products', 'helpers'));
    }

    public function saveBaconSlicing(Request $request, Helpers $helpers)
    {
        $parts = explode(':', $request->product);
        $manual = $request->manual_weight == 'on';

        // dd($request->all());    

        try {
            //insert 
            $id = DB::table('bacon_slicing')->insertGetId([
                'item_code' => $parts[1],
                'actual_weight' => $request->reading,
                'net_weight' => $request->net,
                'process_code' => $parts[3],
                'product_type' => $parts[4],
                'no_of_pieces' => $request->no_of_pieces ?? 0,
                'no_of_crates' => $request->total_crates,
                'user_id' => Auth::id(),
            ]);

            $data = [
                'product_code' =>$parts[1],
                'net_weight' => $request->net,
                'total_pieces' => $request->no_of_pieces ?? 0,
                'production_process' =>  $parts[6],
                'production_date' => today()->toDateString(),
                'timestamp' => now()->toDateTimeString(),
                'id' => $id,
            ];

            // Publish data to RabbitMQ
            //$helpers->publishToQueue($data, 'production_data_bacon_slicing.bc');

            Toastr::success("Slicing bacon entry : {$request->item_id} inserted successfully", 'Success');
            return redirect()
                ->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            Log::error('An exception occurred in ' . __FUNCTION__, ['exception' => $e]);
            return back();
        }
    }
}
