<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use App\Models\User;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HighCare1Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check')->except([]);
    }

    public function index()
    {
        $title = "Todays-Entries";

        $transfers = DB::table('idt_transfers')
            ->whereDate('idt_transfers.created_at', today())            
            ->whereIn('idt_transfers.transfer_from', ['2595'])
            ->select(DB::raw('SUM(idt_transfers.receiver_total_pieces) as received_pieces'), DB::raw('SUM(idt_transfers.receiver_total_weight) as received_weight'), DB::raw('SUM(idt_transfers.total_pieces) as issued_pieces'), DB::raw('SUM(idt_transfers.total_weight) as issued_weight'))
            ->groupBy('idt_transfers.transfer_from')
            ->get();

        return view('highcare1.dashboard', compact('title', 'transfers'));
    }

    public function getIdt(Helpers $helpers)
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
            ->where('idt_transfers.transfer_from', '2595')
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->get();

        return view('highcare1.idt', compact('title', 'items', 'transfer_lines', 'helpers'));
    }

    public function idtReport(Helpers $helpers, $filter=null)
    {
        $title = "IDT-Report";

        $transfer_lines = DB::table('idt_transfers')
            ->leftJoin('items', 'idt_transfers.product_code', '=', 'items.code')
            ->leftJoin('users', 'idt_transfers.user_id', '=', 'users.id')
            ->select('idt_transfers.*', 'items.description as product', 'items.qty_per_unit_of_measure', 'items.unit_count_per_crate', 'users.username')
            ->when($filter == 'today', function ($q) {
                $q->whereDate('idt_transfers.created_at', today()); // today only
            })
            ->when($filter == '', function ($q) {
                $q->whereDate('idt_transfers.created_at', '>=', now()->subDays(7)); // today plus last 7 days
            })
            ->where('idt_transfers.transfer_from', '=', '2595')
            ->orderBy('idt_transfers.created_at', 'DESC')
            ->get();

        return view('highcare1.idt-report', compact('title', 'filter', 'transfer_lines', 'helpers'));
    }

    private function getLocationCode($export_status, $location_code)
    {
        $location = $location_code;

        if($export_status == 1){
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
                'transfer_from' => '2595',
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
}
