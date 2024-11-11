<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FreshcutsBulkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $title = "dashboard";

        $transfers = DB::table('idt_transfers')
            ->whereDate('idt_transfers.created_at', today())
            ->whereIn('idt_transfers.transfer_from', ['1570'])
            ->select(DB::raw('SUM(idt_transfers.receiver_total_pieces) as received_pieces'), DB::raw('SUM(idt_transfers.receiver_total_weight) as received_weight'), DB::raw('SUM(idt_transfers.total_pieces) as issued_pieces'), DB::raw('SUM(idt_transfers.total_weight) as issued_weight'))
            ->groupBy('idt_transfers.transfer_from')
            ->get();

        return view('fresh_bulk.dashboard', compact('title', 'transfers'));
    }

    public function getIdt(Helpers $helpers)
    {
        $title = "IDT";

        $configs = Cache::remember('freshcuts_configs', now()->addMinutes(120), function () {
            return DB::table('scale_configs')
                ->where('section', 'fresh_bulk')
                ->where('scale', 'Fresh&bulk issue')
                ->select('scale', 'tareweight', 'comport')
                ->get()->toArray();
        });

        // Query 1
        $items = Cache::remember('items_freshbulk', now()->addHours(10), function () {
            return DB::table('items')
                ->where('blocked', '!=', 1)
                ->select('code', 'barcode', 'description')
                ->get();
        });

        //receiving users
        $receipt_users = Cache::remember('idt_receipt_users', now()->addHours(10), function () {
            return DB::table('users')
                ->where('barcode_id', '!=', null)
                ->select('id', 'username', 'barcode_id', 'section')
                ->get();
        });

        // Query 2
        $products = Cache::remember('products_freshbulk', now()->addHours(10), function () {
            return DB::table('products')
                ->select(DB::raw('TRIM(code) as code'), 'description')
                ->addSelect(DB::raw("'' as barcode"))
                ->get();
        });

        // Merge the two collections
        $combinedResult = $items->concat($products);

        $tags = Cache::remember('tags', now()->addHours(10), function () {
            return DB::table('receipts')
                ->select('vendor_tag')
                ->where('vendor_tag', '!=', '')
                ->distinct()
                ->get();
        });

        $transfer_lines = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->leftJoin('products', 'idt_transfers.product_code', '=', 'products.code')
            ->leftJoin('users', 'idt_transfers.received_by', '=', 'users.id')
            ->select('idt_transfers.*', 'items.description as product', 'products.description as product2', 'items.qty_per_unit_of_measure', 'items.unit_count_per_crate', 'users.username')
            ->whereDate('idt_transfers.created_at', today())
            ->where('idt_transfers.transfer_from', '1570')
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->get();

        return view('fresh_bulk.idt', compact('title', 'combinedResult', 'transfer_lines', 'configs', 'helpers', 'tags', 'receipt_users'));
    }

    public function createIdt(Request $request, Helpers $helpers)
    {
        // dd($request->all());

        switch ($request->transfer_type) {
            case '1':
                $location = $request->transfer_to; //export
                $desc = $request->desc1;
                break;

            case '2':
                $location = '3600'; //export
                $desc = $request->desc;
                break;

            default:
                # code...
                $location = '3535';
                $desc = $request->desc1;
                break;
        }

        try {
            // try save
            if ($location == '2055') {
                # save for sausage
                DB::table('idt_transfers')->insert([
                    'product_code' => $request->product,
                    'location_code' => $location, //transfer to dispatch default
                    'chiller_code' => $request->chiller_code ?: 'C',
                    'total_pieces' => $request->no_of_pieces ?: 0,
                    'total_weight' => $request->net,
                    'total_crates' => $request->no_of_crates ?: 0,
                    'black_crates' => $request->black_crates,
                    'full_crates' => $request->no_of_crates ?: 0,
                    'incomplete_crate_pieces' => 0,
                    'transfer_type' => $request->transfer_type,
                    'transfer_from' => '1570',
                    'description' => $desc,
                    'order_no' => $request->order_no,
                    'batch_no' => $request->batch_no,
                    'user_id' => Auth::id(),

                    //receiver
                    'receiver_total_pieces' => $request->no_of_pieces ?: 0,
                    'receiver_total_weight' => $request->net,
                    'received_by' => $request->receiver_id,
                    'with_variance' => 1,
                ]);
                
            } elseif ($location == '2500') {
                # save for curing
                DB::table('idt_transfers')->insert([
                    'product_code' => $request->product,
                    'location_code' => $location, //transfer to dispatch default
                    'chiller_code' => $request->chiller_code ?: 'C',
                    'total_pieces' => $request->no_of_pieces ?: 0,
                    'total_weight' => $request->net,
                    'total_crates' => $request->no_of_crates ?: 0,
                    'black_crates' => $request->black_crates,
                    'full_crates' => $request->no_of_crates ?: 0,
                    'incomplete_crate_pieces' => 0,
                    'transfer_type' => $request->transfer_type,
                    'transfer_from' => '1570',
                    'description' => $desc,
                    'order_no' => $request->order_no,
                    'batch_no' => $request->batch_no,
                    'user_id' => Auth::id(),

                    //receiver
                    'receiver_total_pieces' => $request->no_of_pieces ?: 0,
                    'receiver_total_weight' => $request->net,
                    'received_by' => $request->receiver_id,
                    'with_variance' => 1,
                ]);

                $data = [
                    'product_code' => $request->product,
                    'transfer_from_location' => 1570,
                    'transfer_to_location' => 2055,
                    'receiver_total_pieces' => $request->no_of_pieces ?: 0,
                    'receiver_total_weight' => $request->net,
                    'received_by' => Auth::id(),
                    'production_date' => today(),
                ];
    
                // Publish data to RabbitMQ
                $helpers->publishToQueue($data, 'production_data_transfer.bc');

            } else {
                //for despatch
                DB::table('idt_transfers')->insert([
                    'product_code' => $request->product,
                    'location_code' => $location, //transfer to dispatch default
                    'chiller_code' => $request->chiller_code,
                    'total_pieces' => $request->no_of_pieces ?: 0,
                    'total_weight' => $request->net,
                    'total_crates' => $request->no_of_crates ?: 0,
                    'black_crates' => $request->black_crates,
                    'full_crates' => $request->no_of_crates ?: 0,
                    'incomplete_crate_pieces' => 0,
                    'transfer_type' => $request->transfer_type,
                    'transfer_from' => '1570',
                    'description' => $desc,
                    'order_no' => $request->order_no,
                    'batch_no' => $request->batch_no,
                    'user_id' => Auth::id(),
                ]);
            }

            Toastr::success('IDT Transfer recorded successfully', 'Success');
            return redirect()
                ->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }

    public function cancelIdtIssue(Request $request, Helpers $helpers)
    {
        $received = DB::table('idt_transfers')->where('id', $request->item_id)->where('received_by', '!=', null)->exists();

        // check if transfer has been accepted first
        if ($received) {
            // Record has already been received
            Toastr::warning("failed cancel transfer because Transfer id: {$request->item_id} has already been received at despatch", "Warning!");
            return back();
        }

        try {

            DB::transaction(function () use ($request, $helpers,) {
                //update idt issue
                DB::table('idt_transfers')->where('id', $request->item_id)
                    ->update([
                        'total_pieces' => 0,
                        'total_weight' => 0,
                        'edited' => 1,
                    ]);

                //insert change logs
                DB::table('idt_changelogs')->insert([
                    'table_name' => 'idt_transfers',
                    'item_id' => $request->item_id,
                    'changed_by' => Auth::id(),
                    'total_pieces' => 0,
                    'total_weight' => 0,
                    'previous_pieces' => 0,
                    'previous_weight' => $request->weight_edit,
                ]);
            });

            Toastr::success("IDT Transfer id: {$request->item_id} Cancelled successfully", 'Success');
            return redirect()
                ->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back();
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
            ->leftJoin('products', 'idt_transfers.product_code', '=', 'products.code')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->leftJoin('users', 'idt_transfers.user_id', '=', 'users.id')
            ->select('idt_transfers.*', 'items.description as product', 'products.description as product2', 'items.qty_per_unit_of_measure', 'items.unit_count_per_crate', 'users.username')
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
