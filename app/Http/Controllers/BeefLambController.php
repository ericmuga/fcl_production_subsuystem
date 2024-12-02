<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BeefLambController extends Controller
{
    protected $layout = 'beef';

    public function __construct()
    {
        $this->middleware('auth');
        view()->share('layout', $this->layout);
    }

    public function index(Helpers $helpers)
    {
        $title = "dashboard";

        return view('beef_lamb.dashboard', compact('title', 'helpers'));
    }

    public function getBeefSlicing()
    {
        $title = 'Beef';

        $configs = DB::table('scale_configs')
            ->where('scale', 'BeefSlicing')
            ->select('scale', 'tareweight', 'comport')
            ->get()->toArray();

        $products = DB::table('beef_product_processes')
            ->leftJoin('beef_lamb_items', 'beef_product_processes.product_code', '=', 'beef_lamb_items.code')
            ->leftJoin('processes', 'beef_product_processes.process_code', '=', 'processes.process_code')
            ->leftJoin('product_types', 'beef_product_processes.product_type', '=', 'product_types.code')
            ->select('beef_product_processes.product_code', 'beef_product_processes.process_code', 'beef_product_processes.product_type', 'beef_lamb_items.description', 'processes.shortcode', 'processes.process', 'product_types.description as type_description')
            ->get();

        $entries = DB::table('beef_slicing')
            ->whereDate('beef_slicing.created_at', today())
            ->join('beef_lamb_items', 'beef_slicing.item_code', '=', 'beef_lamb_items.code')
            ->join('processes', 'beef_slicing.process_code', '=', 'processes.process_code')
            ->select('beef_slicing.*', 'beef_lamb_items.description', 'processes.process')
            ->orderByDesc('id')
            ->get();

        return view('beef_lamb.slicing_beef', compact('title', 'products', 'configs', 'entries'));
    }

    public function saveBeefSlicing(Request $request, Helpers $helpers)
    {
        $parts = explode(':', $request->product);
        $manual = $request->manual_weight == 'on';

        try {
            //insert 
            DB::table('beef_slicing')->insert([
                'item_code' => $parts[1],
                'scale_reading' => $request->reading,
                'net_weight' => $request->net,
                'process_code' => $parts[3],
                'product_type' => $parts[4],
                'no_of_pieces' => $request->no_of_pieces ?? 0,
                'no_of_crates' => $request->total_crates,
                'black_crates' => $request->black_crates,
                'production_date' => Carbon::createFromFormat('d/m/Y', $request->prod_date),
                'location_code' => '1570',
                'transfer_from' => 'B3535',
                'manual_weight' => $manual,
                'user_id' => Auth::id(),
            ]);

            Toastr::success("Slicing beef entry : {$request->item_id} inserted successfully", 'Success');
            return redirect()
                ->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            Log::error('An exception occurred in ' . __FUNCTION__, ['exception' => $e]);
            return back();
        }
    }

    public function getIdtReceiving(Request $request)
    {
        $title = 'InterCompany Transfers';

        $configs = DB::table('scale_configs')
            ->where('scale', 'BeefReceiving')
            ->select('scale', 'tareweight', 'comport')
            ->get()->toArray();

        $products = DB::table('beef_lamb_items')
            ->select('code', 'description')
            ->get();

        $entries = DB::table('idt_transfers as a')
            ->join('beef_lamb_items as b', 'a.product_code', '=', 'b.code')
            ->join('users as c', 'a.received_by', '=', 'c.id')
            ->select('a.id', 'a.product_code', 'a.description as vehicle_no', 'b.description', 'a.receiver_total_crates', 'a.receiver_total_pieces', 'a.receiver_total_weight', 'a.batch_no', 'c.username as received_by', 'a.production_date', 'a.created_at')
            ->where('a.location_code', '1570')
            ->whereDate('a.created_at', today())
            ->get();

        return view('beef_lamb.idt-receiving', compact('title', 'products', 'configs', 'entries'));
    }

    public function getIdtReceivingV2(Request $request, Helpers $helpers)
    {
        $title = 'InterCompany Transfers';

        $configs = DB::table('scale_configs')
            ->where('scale', 'BeefReceiving')
            ->select('scale', 'tareweight', 'comport')
            ->get()->toArray();

        $entries = DB::table('idt_transfers')
            ->leftJoin('users', 'idt_transfers.received_by', '=', 'users.id')
            ->select('idt_transfers.*', 'users.username as received_by')
            ->where('idt_transfers.location_code', '1570')
            ->whereDate('idt_transfers.created_at', '>=', today()->subDays(2))
            ->orderByDesc('idt_transfers.created_at')
            ->get();

        return view('beef_lamb.idt-receivingv2', compact('title', 'configs', 'entries', 'helpers'));
    }

    public function updateIdtReceiving(Request $request, Helpers $helpers)
    {
        $manual = $request->manual_weight == 'on';

        try {
            //update
            DB::table('idt_transfers')
                ->where('id', $request->transfer_id)
                ->update([
                    'manual_weight' => $manual,
                    'receiver_total_crates' => $request->total_crates ?? 0,
                    'receiver_total_pieces' => $request->no_of_pieces ?? 0,
                    'receiver_total_weight' => $request->net,
                    'production_date' => $request->prod_date,
                    'received_by' => Auth::id(),
                    'with_variance' => $request->with_variance ?? 0,
                ]); 
            $data = [
                'product_code' => $request->product,
                'transfer_from_location' => $request->from_location,
                'transfer_to_location' => 1570,
                'receiver_total_pieces' => $request->no_of_pieces ?? 0,
                'receiver_total_weight' => $request->net,
                'production_date' => $request->prod_date,
                'received_by' => Auth::id(),
                'production_date' => $request->prod_date,
                'with_variance' => 0,
            ];

            // Publish data to RabbitMQ
            $data['timestamp'] = now()->toDateTimeString();
            $helpers->publishToQueue($data, 'production_data_transfer.bc');

            return response()->json(['success' => true, 'message' => 'Transfer updated successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error updating transfer']);
            Log::error('An exception occurred in ' . __FUNCTION__, ['exception' => $e]);
            return back();
        }
    }

    public function saveIdtReceiving(Request $request, Helpers $helpers)
    {
        // dd($request->all());

        $user = Auth::id();

        $manual = $request->manual_weight == 'on';

        try {
            //insert 
            DB::table('idt_transfers')->insert([
                'product_code' => $request->product,
                'batch_no' => $request->batch_no,
                'total_crates' => $request->total_crates,
                'total_pieces' => $request->no_of_pieces ?? 0,
                'total_weight' => $request->net,
                'receiver_total_crates' => $request->total_crates,
                'receiver_total_pieces' => $request->no_of_pieces ?? 0,
                'receiver_total_weight' => $request->net,
                'production_date' => Carbon::createFromFormat('d/m/Y', $request->prod_date),
                'location_code' => '1570',
                'description' => $request->description,
                'manual_weight' => $manual,
                'user_id' => $user,
                'received_by' => $user,
            ]);

            $data = [
                'product_code' => $request->product,
                'transfer_from_location' => 'B3535',
                'transfer_to_location' => 1570,
                'receiver_total_pieces' => $request->no_of_pieces ?? 0,
                'receiver_total_weight' => $request->net,
                'received_by' => Auth::id(),
                'production_date' => Carbon::createFromFormat('d/m/Y', $request->prod_date),
                'with_variance' => 0,
            ];

            // Publish data to RabbitMQ
            $data['timestamp'] = now()->toDateTimeString();
            $helpers->publishToQueue($data, 'production_data_transfer.bc');

            Toastr::success("Beef/lamb IDT entry for : {$request->product} inserted successfully", 'Success');
            return redirect()
                ->back()
                ->withInput();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            Log::error('An exception occurred in ' . __FUNCTION__, ['exception' => $e]);
            return back();
        }
    }
}
