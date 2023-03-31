<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChoppingController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check');
    }

    public function choppingCreateBatch(Request $request, Helpers $helpers)
    {
        $title = "Chopping-Batch";

        $date_filter = today()->subDays(7);

        $templates = DB::table('template_header')
            ->where('template_lines.main_product', 'Yes')
            ->leftJoin('template_lines', 'template_header.template_no', '=', 'template_lines.template_no')
            ->select('template_header.template_no', 'template_header.template_name', 'template_lines.description as template_output')
            ->get();

        return view('chopping.create-batch', compact('title', 'date_filter', 'templates', 'helpers'));
    }

    public function choppingSaveBatch(Request $request, Helpers $helpers)
    {
        dd($request->all());
        $temp_no = strtok($request->temp_no,  '-');

        try {
            //insert batch
            DB::table('batches')->insert([
                'batch_no' => $request->batch_no,
                'template_no' => $temp_no,
                'output_quantity' => $request->output_qty,
                'status' => $request->status,
                'user_id' => $helpers->authenticatedUserId(),
            ]);

            // get template lines
            $temp_lines = DB::table('template_lines')->where('template_no', $temp_no)
                ->select('item_code', 'percentage')
                ->get();

            if (!empty($temp_lines)) {
                foreach ($temp_lines as $tl) {
                    DB::table('production_lines')->insert([
                        'batch_no' => $request->batch_no,
                        'item_code' => $tl->item_code,
                        'template_no' => $temp_no,
                        // 'quantity' => ($tl->percentage / 100) * $request->output_qty,
                    ]);
                }
            }

            Toastr::success("Chopping Batch {$request->batch_no} created successfully", "Success");
            return redirect()
                ->route('chopping_batches_list');
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back();
        }
    }

    public function batchLists(Helpers $helpers, $filter = null)
    {
        dd('here');

        $title = "Spices-Batches";

        $date_filter = today()->subDays(7);

        $templates = DB::table('template_header')
            ->where('template_lines.main_product', 'Yes')
            ->leftJoin('template_lines', 'template_header.template_no', '=', 'template_lines.template_no')
            ->select('template_header.template_no', 'template_header.template_name', 'template_lines.description as template_output')
            ->get();

        $batches = DB::table('batches')
            ->where('template_lines.main_product', 'Yes')
            // ->whereDate('template_lines.created_at', $date_filter) //last 7 days
            ->leftJoin('users', 'batches.user_id', '=', 'users.id')
            ->leftJoin('template_header', 'batches.template_no', '=', 'template_header.template_no')
            ->leftJoin('template_lines', 'batches.template_no', '=', 'template_lines.template_no')
            ->select('batches.*', 'users.username', 'template_header.template_name', 'template_lines.description as template_output')
            ->when($filter == 'open' || $filter == '', function ($q) {
                $q->where('batches.status', '=', 'open'); // open batches
            })
            ->when($filter == 'posted', function ($q) {
                $q->where('batches.status', '=', 'posted'); // posted batches
            })
            ->when($filter == 'closed', function ($q) {
                $q->where('batches.status', '=', 'closed'); // closed batches
            })
            ->orderBy('batches.created_at', 'DESC')
            ->get();

        return view('spices.batches', compact('title', 'filter', 'templates', 'batches', 'helpers', 'date_filter'));
    }
}
