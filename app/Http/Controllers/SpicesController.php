<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use App\Models\TemplateHeader;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SpicesController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check');
    }

    public function index()
    {
        $title = "dashboard";

        return view('spices.dashboard', compact('title'));
    }

    public function templateList()
    {
        $title = "Template List";

        $templates = DB::table('template_header')->get();

        return view('spices.template-list', compact('title', 'templates'));
    }

    public function templateLines(Request $request)
    {
        $title = "Template Lines";

        $temp_no = $request->template_no;

        $template_lines = DB::table('template_lines')->where('template_no', $temp_no)->get();


        return view('spices.template-lines', compact('title', 'template_lines', 'temp_no'));
    }

    public function importReceipts(Request $request, Helpers $helpers)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt',

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
            //code...
            DB::transaction(function () use ($request, $helpers) {

                $fileD = fopen($request->file, "r");
                // $column = fgetcsv($fileD); // skips first row as header

                while (!feof($fileD)) {
                    $rowData[] = fgetcsv($fileD);
                }

                //delete template headers and lines
                DB::table('template_header')->delete();
                DB::table('template_lines')->delete();

                foreach ($rowData as $key => $row) {
                    //get Template no from header
                    $template = TemplateHeader::firstOrCreate(
                        ['template_no' =>  $row[0]],
                        ['template_name' => $row[0], 'user_id' => $helpers->authenticatedUserId()],
                    );

                    DB::table('template_lines')->insert(
                        [
                            'template_no' => $template->template_no,
                            'item_code' => $row[1],
                            'percentage' => $row[2],
                            'type' => $row[3],
                            'main_product' => $row[4],
                            'shortcode' => $row[5],
                            'location' => $row[6],
                            'unit_measure' => $row[7],
                            'description' => $row[8],
                        ]
                    );
                }
            });

            Toastr::success('Template Header and Lines uploaded successfully', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error Occurred. Wrong Data format!. Records not saved!');
            return back();
        }
    }

    public function createBatchLines(Request $request, Helpers $helpers)
    {
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
                        'quantity' => ($tl->percentage / 100) * $request->output_qty,
                    ]);
                }
            }

            Toastr::success("Batch {$request->batch_no} added successfully", "Success");
            return redirect()
                ->route('batches_list');
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back();
        }
    }

    public function batchLists(Helpers $helpers, $filter = null)
    {
        $title = "Batches";

        $templates = DB::table('template_header')
            ->where('template_lines.main_product', 'Yes')
            ->leftJoin('template_lines', 'template_header.template_no', '=', 'template_lines.template_no')
            ->select('template_header.template_no', 'template_header.template_name', 'template_lines.description as template_output')
            ->get();

        $batches = DB::table('batches')
            ->where('template_lines.main_product', 'Yes')
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

        return view('spices.batches', compact('title', 'filter', 'templates', 'batches', 'helpers'));
    }

    public function productionLines($batch_no, Helpers $helpers)
    {
        $title = "Production Lines";

        $table = 'production_lines';

        $lines = DB::table('production_lines')
            ->where('production_lines.batch_no', $batch_no)
            ->leftJoin('batches', 'production_lines.batch_no', '=', 'batches.batch_no')
            ->join('template_lines', function ($join) use ($table) {
                $join->on($table . '.item_code', '=',  'template_lines.item_code');
                $join->on($table . '.template_no', '=', 'template_lines.template_no');
            })
            ->orderBy('production_lines.item_code', 'ASC')
            ->get();

        return view('spices.production-lines', compact('title', 'lines', 'helpers', 'batch_no'));
    }

    public function closeOrPostBatch(Request $request, Helpers $helpers)
    {
        try {
            $route_filter = 'closed';

            if ($request->filter == 'close') {
                //close batch
                DB::table('batches')
                    ->where('batch_no', $request->batch_no)
                    ->update([
                        'status' => 'closed',
                        'closed_by' => $helpers->authenticatedUserId(),
                        'updated_at' => now(),
                    ]);
            } elseif ($request->filter == 'post') {
                $route_filter = 'posted';
                //post batch
                DB::table('batches')
                    ->where('batch_no', $request->batch_no)
                    ->update([
                        'status' => 'posted',
                        'posted_by' => $helpers->authenticatedUserId(),
                        'updated_at' => now(),
                    ]);
            }

            Toastr::success("Action {$request->filter} batch no: {$request->item_name} completed successfully", 'Success');
            return redirect()->route('batches_list', $route_filter);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back();
        }
    }
}
