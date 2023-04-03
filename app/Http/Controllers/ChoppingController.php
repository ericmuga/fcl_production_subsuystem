<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChoppingController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check');
    }

    public function choppingBatches(Request $request, Helpers $helpers)
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
        $temp_no = strtok($request->temp_no,  '-');
        $batch_no = 'ch-' . $request->batch_no;

        try {
            //insert batch
            DB::table('batches')->insert([
                'batch_no' => $batch_no,
                'template_no' => $temp_no,
                'output_quantity' => 0,
                'status' => $request->status,
                'from_batch' => $request->from_batch,
                'to_batch' => $request->to_batch,
                'user_id' => $helpers->authenticatedUserId(),
            ]);

            // get template lines
            $temp_lines = DB::table('template_lines')->where('template_no', $temp_no)
                ->select('item_code', 'percentage')
                ->get();

            if (!empty($temp_lines)) {
                foreach ($temp_lines as $tl) {
                    DB::table('production_lines')->insert([
                        'batch_no' => $batch_no,
                        'item_code' => $tl->item_code,
                        'template_no' => $temp_no,
                        'quantity' => ($tl->percentage / 100) * $request->batch_size,
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
        $title = "Chopping-Batches";

        $date_filter = today()->subDays(7);

        $templates = DB::table('template_header')
            ->where('template_lines.main_product', 'Yes')
            ->leftJoin('template_lines', 'template_header.template_no', '=', 'template_lines.template_no')
            ->select('template_header.template_no', 'template_header.template_name', 'template_lines.description as template_output')
            ->get();

        $batches = DB::table('batches')
            ->where('batch_no', 'LIKE', 'ch-%')
            ->where('template_lines.main_product', 'Yes')
            ->whereDate('batches.created_at', '>=', $date_filter) //last 7 days
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

        return view('chopping.batches', compact('title', 'filter', 'templates', 'batches', 'helpers', 'date_filter'));
    }
}
