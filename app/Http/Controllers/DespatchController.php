<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class DespatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check')->except(['']);
    }

    public function index()
    {
        $title = "dashboard";

        return view('despatch.dashboard', compact('title'));
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

        return view('despatch.idt', compact('title', 'filter', 'transfer_lines', 'items', 'helpers'));
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
}