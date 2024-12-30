<?php

namespace App\Http\Controllers;

use Brian2694\Toastr\Facades\Toastr;
use App\Models\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class IDTController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function listIDTReceive(Request $request, Helpers $helpers)
    {
        $from_location = $request->query('from_location');
        $to_location = $request->query('to_location');

        $title = "Receive IDT from $from_location to $to_location";

        $location_names = [
            '1570' => 'Butchery',
            '2595' => 'Highcare',
            '2055' => 'Sausage',
            '3035' => 'PetFood',
            '3535' => 'Despatch',
        ];

        if ($from_location == null || $to_location == null || !array_key_exists($from_location, $location_names) || !array_key_exists($to_location, $location_names)) {
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
            ->where('idt_transfers.location_code', request()->query('to_location'))
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


        $configs = DB::table('scale_configs')->where('section', $location_names[$to_location])->where('scale', 'IDT')->get();
       
        return view('idt.receive', compact('title', 'configs', 'transfer_lines', 'location_names', 'helpers'));
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
            $helpers->publishToQueue($data, 'production_data_transfer.bc');

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
}