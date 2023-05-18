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

    public function batchLists(Helpers $helpers, $filter = null)
    {
        $title = "Chopping-Batches";

        $date_filter = today()->subDays(7);

        $templates = DB::table('template_header')
            ->where('template_lines.main_product', 'Yes')
            ->Join('template_lines', 'template_header.template_no', '=', 'template_lines.template_no')
            ->select('template_header.template_no', 'template_header.template_name', 'template_lines.description as template_output')
            ->get();

        $batches = DB::table('batches')
            ->where('batch_no', 'LIKE', 'ch-%')
            ->where('template_lines.main_product', 'Yes')
            ->whereDate('batches.created_at', '>=', $date_filter) //last 7 days
            ->leftJoin('users', 'batches.user_id', '=', 'users.id')
            ->leftJoin(
                'template_header',
                'batches.template_no',
                '=',
                'template_header.template_no'
            )
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

    public function choppingSaveBatch(Request $request, Helpers $helpers)
    {
        $temp_no = strtok($request->temp_no,  '-');
        $batch_no = 'ch-' . $request->batch_no;

        try {
            //insert batch
            DB::transaction(function () use ($request, $helpers, $temp_no, $batch_no) {
                DB::table('batches')->insert([
                    'batch_no' => $batch_no,
                    'template_no' => $temp_no,
                    'output_quantity' => 0,
                    'status' => $request->status,
                    'from_batch' => $request->from_batch,
                    'user_id' => $helpers->authenticatedUserId(),
                ]);

                // get template lines
                $temp_lines = DB::table('template_lines')->where('template_no', $temp_no)
                    ->select('item_code', 'units_per_100')
                    ->get();

                if (!empty($temp_lines)) {
                    foreach ($temp_lines as $tl) {
                        DB::table('production_lines')->insert([
                            'batch_no' => $batch_no,
                            'item_code' => $tl->item_code,
                            'template_no' => $temp_no,
                            'quantity' => $tl->units_per_100,
                        ]);
                    }
                }
            });

            Toastr::success("Chopping Batch {$request->batch_no} created successfully", "Success");
            return redirect()
                ->route('chopping_batches_list', $request->status);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back();
        }
    }

    public function productionLines($batch_no, Helpers $helpers, $from_batch)
    {
        $title = "Chopping Production Lines";

        $table = 'production_lines';

        $lines = DB::table('production_lines')
            ->where('production_lines.batch_no', $batch_no)
            ->leftJoin('batches', 'production_lines.batch_no', '=', 'batches.batch_no')
            ->join('template_lines', function ($join) use ($table) {
                $join->on($table . '.item_code', '=',  'template_lines.item_code');
                $join->on($table . '.template_no', '=', 'template_lines.template_no');
            })
            ->select('production_lines.*', 'template_lines.description', 'template_lines.percentage', 'template_lines.type', 'template_lines.main_product', 'template_lines.unit_measure', 'template_lines.units_per_100', 'template_lines.location', 'batches.status')
            ->orderBy('template_lines.type', 'ASC')
            ->get();

        return view('chopping.production-lines', compact('title', 'lines', 'helpers', 'batch_no', 'from_batch'));
    }

    public function postedLinesReport($batch_no, Helpers $helpers, $from_batch)
    {
        $title = "Chopping Production Lines";

        $table = 'production_lines';

        $lines = DB::table('production_lines')
            ->where('production_lines.batch_no', $batch_no)
            ->leftJoin('batches', 'production_lines.batch_no', '=', 'batches.batch_no')
            ->join('template_lines', function ($join) use ($table) {
                $join->on($table . '.item_code', '=',  'template_lines.item_code');
                $join->on($table . '.template_no', '=', 'template_lines.template_no');
            })
            ->select('production_lines.*', 'template_lines.description', 'template_lines.percentage', 'template_lines.type', 'template_lines.main_product', 'template_lines.unit_measure', 'template_lines.units_per_100', 'template_lines.location', 'batches.status')
            ->orderBy('template_lines.type', 'ASC')
            ->get();

        return view('chopping.production-lines', compact('title', 'lines', 'helpers', 'batch_no', 'from_batch'));
    }

    public function updateBatchItems(Request $request)
    {
        try {
            DB::transaction(function () use ($request) {
                //update
                foreach ($request->item_code as $key => $value) {

                    DB::table('production_lines')
                        ->where('batch_no', $request->item_name)
                        ->where('item_code', $value)
                        ->update([
                            'quantity' => $request->qty[$key],
                            'updated_at' => now(),
                        ]);
                }

                DB::table('production_lines')
                    ->where('batch_no', $request->item_name)
                    ->where('item_code', $request->main_item)
                    ->update([
                        'quantity' => $request->total_output,
                        'updated_at' => now(),
                    ]);
            });

            Toastr::success("Update items on batch no: {$request->item_name} completed successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back();
        }
    }

    public function closeOrPostBatch(Request $request, Helpers $helpers)
    {
        try {
            $route_filter = 'closed';

            $to_batch = (int)$request->to_batch;
            $from_batch = (int)$request->from_batch;

            $batch_size = ($to_batch - $from_batch) + 1;

            switch ($request->filter) {
                case 'close':
                    //close batch
                    DB::table('batches')
                        ->where('batch_no', $request->batch_no)
                        ->update([
                            'to_batch' => $request->to_batch,
                            'status' => 'closed',
                            'output_quantity' => $this->getQuantityTotal($request->batch_no, $batch_size, $request->main_item),
                            'closed_by' => $helpers->authenticatedUserId(),
                            'updated_at' => now(),
                        ]);
                    break;

                case 'post':
                    $route_filter = 'posted';
                    //post batch
                    DB::transaction(
                        function () use ($request, $helpers) {
                            //update batch to posted
                            DB::table('batches')
                                ->where('batch_no', $request->batch_no)
                                ->update([
                                    'status' => 'posted',
                                    'posted_by' => $helpers->authenticatedUserId(),
                                    'updated_at' => now(),
                                ]);
                        }
                    );
                    break;

                default:
                    # code...
                    break;
            }

            Toastr::success("Action {$request->filter} batch no: {$request->item_name} completed successfully", 'Success');
            return redirect()->route('chopping_batches_list', $route_filter);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back();
        }
    }

    private function getQuantityTotal($batch_no, $batch_size, $main_item)
    {
        $sum_qty = DB::table('production_lines')
            ->where('batch_no', $batch_no)
            ->where('item_code', '!=', $main_item)
            ->select(DB::raw('SUM(quantity * ' . (int)$batch_size . ') as total_qty'))
            ->value('total_qty');

        return $sum_qty;
    }
}
