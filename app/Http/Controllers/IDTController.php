<?php

namespace App\Http\Controllers;

use Brian2694\Toastr\Facades\Toastr;
use App\Models\Helpers;
use App\Exports\IDTSummaryExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Session;


class IDTController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    const locations = [
        '1570' => 'Butchery',
        '2595' => 'Highcare',
        '2055' => 'Sausage',
        '3035' => 'PetFood',
        '3535' => 'Despatch',
        '4300' => 'Incinerator',
        '4400' => 'Kitchen',
        '4450' => 'QA'
    ];

    public function listIDTReceive(Request $request, Helpers $helpers)
    {
        // dd($request->all());
        $from_location = $request->query('from_location');
        $to_location = $request->query('to_location');

        $title = "Receive IDT from $from_location to $to_location";

        $locations = self::locations;

        if ($from_location == null || $to_location == null || !array_key_exists($from_location, $locations) || !array_key_exists($to_location, $locations)) {
           abort(404);
        };

        // Combine products and items using UNION
        $allItems = DB::table('products')
            ->select('code', 'description') // Select columns from products
            ->union(
                DB::table('items')
                ->select('code', 'description') // Select columns from items
            );

        // Join the combined result with the main table
        $transfer_lines = DB::table('idt_transfers')
            ->leftJoin('users as issuer', 'idt_transfers.user_id', '=', 'issuer.id')
            ->leftJoin('users as receiver', 'idt_transfers.received_by', '=', 'receiver.id')
            ->joinSub($allItems, 'all_items', function($join) {
                $join->on('idt_transfers.product_code', '=', 'all_items.code');
            })
            // Apply filters
            ->whereDate('idt_transfers.created_at', '>=', today()->subDays(2))
            ->where(function ($query) {
                $to_location = request()->query('to_location');
                $query->where('idt_transfers.location_code', $to_location);
                
                if ($to_location == '2595') {
                    $query->orWhere('idt_transfers.location_code', '2500');
                }
            })
            ->where(function ($query) {
                $from_location = request()->query('from_location');
                $query->where('idt_transfers.transfer_from', $from_location);

                if ($from_location == '3535') {
                    $query->orWhere('idt_transfers.transfer_from', '3600') // Export
                          ->orWhere('idt_transfers.transfer_from', '3540') // Third Party
                          ->orWhere('idt_transfers.transfer_from', '3555'); // Old Factory
                }
            })
            ->where(function ($query) {
                $query->where('idt_transfers.requires_approval', 0)
                      ->orWhere(function ($query) {
                          $query->where('idt_transfers.requires_approval', 1)
                                ->where('idt_transfers.approved', 1);
                      });
            })
            // Select columns from the joined tables
            ->select(
                'idt_transfers.*', // Select all columns from idt_transfers
                'all_items.description', 'all_items.code', // Columns from the all_items subquery
                'issuer.username as issued_by', // Alias for issuer username
                'receiver.username as received_by' // Alias for receiver username
            )
            // Order by the creation date
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->get();


        $configs = DB::table('scale_configs')->where('section', $locations[$to_location])->where('scale', 'IDT')->get();
       
        return view('idt.receive', compact('title', 'configs', 'transfer_lines', 'locations', 'helpers'));
    }

    public function updateReceiveIdt(Request $request, Helpers $helpers)
    {
        $transfer = DB::table('idt_transfers')
            ->where('id', $request->transfer_id)
            ->first();

        try {
            // try update
            DB::table('idt_transfers')
                ->where('id', $request->transfer_id)
                ->update([
                    'receiver_total_pieces' => $request->receiver_total_pieces,
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

    public function listIDTIssued(Request $request, Helpers $helpers)
    {
        $title = "Issue IDT";

        $from_location = $request->query('from_location');

        $locations = self::locations;

        if ($from_location == null || !array_key_exists($from_location, $locations)) {
            abort(404);
        };

        // Combine products and items using UNION
        $allItems = DB::table('products')
            ->select('code', 'description') // Select columns from products
            ->union(
                DB::table('items')
                ->select('code', 'description') // Select columns from items
            );

        $chillers = DB::table('chillers')->get();

        // Join the combined result with the main table
        $transfer_lines = DB::table('idt_transfers')
            ->leftJoin('users as issuer', 'idt_transfers.user_id', '=', 'issuer.id')
            ->leftJoin('users as receiver', 'idt_transfers.received_by', '=', 'receiver.id')
            ->joinSub($allItems, 'all_items', function($join) {
                $join->on('idt_transfers.product_code', '=', 'all_items.code');
            })
            // Apply filters
            ->whereDate('idt_transfers.created_at', '>=', today()->subDays(2))
            ->where('idt_transfers.transfer_from', request()->query('from_location'))
            // Select columns from the joined tables
            ->select(
                'idt_transfers.*', // Select all columns from idt_transfers
                'all_items.description', 'all_items.code', // Columns from the all_items subquery
                'issuer.username as issued_by', // Alias for issuer username
                'receiver.username as received_by' // Alias for receiver username
            )
            // Order by the creation date
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->get();

        if ($from_location == '3035') {
            $petfood_item_codes = ['J31080101', 'J31080106', 'J31080201', 'J31080302', 'J31090171'];
            $products = DB::table('items')->whereIn('code', $petfood_item_codes)->get();
        } elseif ($from_location == '3535') {
            $products = DB::table('items')->get();
        } else {
            $products = DB::table('products')->get();
        }
        
        $configs = DB::table('scale_configs')->where('section', $locations[$from_location])->where('scale', 'IDT')->get();
       
        return view('idt.issue', compact('title', 'configs', 'products', 'chillers', 'transfer_lines', 'locations', 'helpers'));
    }

    public function saveIssueIdt(Request $request) {
        try {
            DB::table('idt_transfers')->insert([
                'product_code' => $request->product_code,
                'location_code' => $request->transfer_type == 1 ? '3600' : $request->location_code,
                'chiller_code' => $request->chiller_code,
                'total_pieces' => $request->no_of_pieces ?: 0,
                'total_weight' => $request->net,
                'total_crates' => $request->total_crates ?: 0,
                'black_crates' => $request->black_crates ?: 0,
                'full_crates' => $request->total_crates ?: 0,
                'incomplete_crate_pieces' => $request->incomplete_pieces ?: 0,
                'transfer_type' => 0,
                'transfer_from' => $request->transfer_from,
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

    public function approveIdt(Request $request, Helpers $helpers)
    {
       try {
            $transfer = DB::table('idt_transfers')
                ->where('id', $request->id)
                ->first();

            if ($request->narration != null) {
                $narration = $transfer->description . " Approval Narration: " . $request->narration;
            } else {
                $narration = $transfer->description;
            }

            // update approval status for transfer
            DB::table('idt_transfers')
                ->where('id', $request->id)
                ->update([
                    'approved' => $request->input('approve'),
                    'approved_by' => Auth::id(),
                    'updated_at' => now(),
                    'description' => $narration,
                ]);
            
            if ($request->input('approve') == 1) {
                Toastr::success('IDT Transfer approved successfully', 'Success');
            } else {
                Toastr::warning('IDT Transfer rejected successfully', 'Success');
            };

            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            $helpers->CustomErrorlogger($e->getMessage(),  __FUNCTION__);
            return back()
                ->withInput();
        }
    }

    public function beefCombinedReport(Request $request, Helpers $helpers, $filter = null)
    {
        $title = "IDT Beef Combined Report";

        $q =  DB::table('idt_transfers as transfers')
            ->leftJoin('beef_lamb_items as items', 'transfers.product_code', '=', 'items.code')
            ->where('transfers.transfer_from', 'B3535')
            ->where('transfers.location_code', '1570')
            ->select(
                'product_code',
                DB::raw('SUM(CASE WHEN receiver_total_weight IS NULL THEN total_weight ELSE 0 END) as sent_weight'),
                DB::raw('SUM(receiver_total_weight) as received_weight'),
                DB::raw('SUM(CASE WHEN receiver_total_pieces IS NULL THEN total_pieces ELSE 0 END) as sent_pieces'),
                DB::raw('SUM(receiver_total_pieces) as received_pieces'),
                'items.description as item_description',
            )
            ->groupBy('product_code', 'items.description'); // Group by item code

        if ($request->from_date) {
            $q->whereDate('transfers.created_at', '>=', $request->from_date);
            $title .= ' from ' . $request->from_date;
        }
    
        if ($request->to_date) {
            $q->whereDate('transfers.created_at', '<=', $request->to_date);
            $title .= ' to ' . $request->from_date;
        }

        if (!$request->from_date && !$request->to_date) {
            $q->whereDate('transfers.created_at', '>=', now()->subDays(30));
            $title .= " for the last 30 days";
        }

        $summary = $q->get();

        $products = Cache::remember('cm_items', now()->addMinutes(120), function () {
            return DB::table('products')->get();
        });

        return view('idt.beef-combined-report', compact('title', 'helpers', 'summary', 'products'));
    }

    public function beefCombinedExport(Request $request, Helpers $helpers)
    {
        $title = 'IDT Beef Combined Report';

        $q =  DB::table('idt_transfers as transfers')
        ->leftJoin('beef_lamb_items as items', 'transfers.product_code', '=', 'items.code')
        ->where('transfers.transfer_from', 'B3535')
        ->where('transfers.location_code', '1570')
        ->select(
            'product_code',
            'items.description as item_description',
            DB::raw('SUM(CASE WHEN receiver_total_weight IS NULL THEN total_weight ELSE 0 END) as sent_weight'),
            DB::raw('SUM(receiver_total_weight) as received_weight'),
            DB::raw('SUM(CASE WHEN receiver_total_pieces IS NULL THEN total_pieces ELSE 0 END) as sent_pieces'),
            DB::raw('SUM(receiver_total_pieces) as received_pieces'),
        )
        ->groupBy('product_code', 'items.description'); // Group by item code

    if ($request->from_date) {
        $q->whereDate('transfers.created_at', '>=', $request->from_date);
        $title .= ' from ' . $request->from_date;
    }

    if ($request->to_date) {
        $q->whereDate('transfers.created_at', '<=', $request->to_date);
        $title .= ' to ' . $request->from_date;
    }

    if (!$request->from_date && !$request->to_date) {
        $q->whereDate('transfers.created_at', '>=', now()->subDays(30));
        $title .= " for the last 30 days";
    }

    $data = $q->get();

        $exports = Session::put('session_export_data', $data);

        return Excel::download(new IDTSummaryExport, $title . '.xlsx');
    }
}