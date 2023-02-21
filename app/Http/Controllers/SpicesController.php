<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use App\Models\TemplateHeader;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        $stocks = DB::table('spices_stock')
            ->sum('spices_stock.quantity');

        $todays = DB::table('spices_stock')
            ->select(
                DB::raw(" (SELECT COALESCE(SUM(spices_stock.quantity),0) FROM spices_stock
                    WHERE entry_type=1 and convert(varchar(10), created_at, 102) 
                                = convert(varchar(10), getdate(), 102)) as incoming_stocks"),
                DB::raw(" (SELECT COALESCE(SUM(spices_stock.quantity),0) FROM spices_stock
                    WHERE entry_type=2 and convert(varchar(10), created_at, 102) 
                                = convert(varchar(10), getdate(), 102)) as consumed_stocks")
            )
            ->limit(1)
            ->get();

        return view('spices.dashboard', compact('title', 'stocks', 'todays'));
    }

    public function templateList()
    {
        $title = "Template List";

        $templates = DB::table('template_header')->get();

        return view('spices.template-list', compact('title', 'templates'));
    }

    public function itemsList()
    {
        $title = "Items List";

        $items = DB::table('spices_items')->get();

        return view('spices.items', compact('title', 'items'));
    }

    public function stockList()
    {
        $title = "Stock List";

        $stock = DB::table('spices_stock')
            ->leftJoin('spices_items', 'spices_stock.item_code', '=', 'spices_items.code')
            ->select(
                'spices_stock.item_code',
                'spices_items.code',
                'spices_items.description',
                'spices_items.unit_measure',
                DB::raw('SUM(spices_stock.quantity)  as book_stock')
            )
            ->groupBy('spices_stock.item_code', 'spices_items.code', 'spices_items.description', 'spices_items.unit_measure',)
            ->orderBy('spices_stock.item_code')
            ->get();

        return view('spices.stocks', compact('title', 'stock'));
    }

    public function stockLines(Helpers $helpers, $filter = null)
    {
        $title = "Stock Lines";

        switch ($filter) {
            case 'incoming':
                $range_filter = 'Todays incoming';
                break;

            case 'consumed':
                $range_filter = 'Todays consumed';
                break;

            default:
                # code...
                $range_filter = 'All';
                break;
        }

        $lines = DB::table('spices_stock')
            ->leftJoin('users', 'spices_stock.user_id', '=', 'users.id')
            ->leftJoin('spices_items', 'spices_stock.item_code', '=', 'spices_items.code')
            ->select('spices_stock.*', 'users.username as user', 'spices_items.code', 'spices_items.description', 'spices_items.unit_measure')
            ->when($filter == '', function ($q) {
                $q->where('spices_stock.entry_type', '!=', null); // all
            })
            ->when($filter == 'incoming', function ($q) {
                $q->where('spices_stock.entry_type', '=', 1)
                    and $q->whereDate('spices_stock.created_at', today()); // incoming
            })
            ->when($filter == 'consumed', function ($q) {
                $q->where('spices_stock.entry_type', '=', 2)
                    and $q->whereDate('spices_stock.created_at', today()); // consumed
            })
            ->get();

        return view('spices.stock-lines', compact('title', 'lines', 'helpers', 'range_filter'));
    }

    public function physicalStock(Helpers $helpers)
    {
        $title = "Physical Stocks";

        $lines = DB::table('physical_stocks')
            ->leftJoin('spices_items', 'physical_stocks.item_code', '=', 'spices_items.code')
            ->leftJoin('users', 'physical_stocks.user_id', '=', 'users.id')
            ->orderBy('physical_stocks.created_at', 'DESC')
            ->select('physical_stocks.*', 'users.username as user', 'spices_items.code', 'spices_items.description', 'spices_items.unit_measure')
            ->get();

        $items = Cache::rememberForever('physical_stock_list', function () {
            return DB::table('spices_items')->select('code', 'description')->get();
        });

        return view('spices.physical-stocks', compact('title', 'items', 'lines', 'helpers'));
    }

    public function addPhysicalStock(Request $request, Helpers $helpers)
    {
        // dd($request->all());
        try {
            //save ...
            DB::transaction(function () use ($request, $helpers) {
                DB::table('physical_stocks')->insert([
                    'item_code' => $request->item_code,
                    'quantity' => $request->quantity,
                    'status' => '1',
                    'user_id' => $helpers->authenticatedUserId(),
                ]);
            });

            Toastr::success('New physical Stock entry saved successfully', 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back();
        }
    }

    public function templateLines(Request $request)
    {
        $title = "Template Lines";

        $temp_no = $request->template_no;

        $template_lines = DB::table('template_lines')->where('template_no', $temp_no)->get();


        return view('spices.template-lines', compact('title', 'template_lines', 'temp_no'));
    }

    public function importTemplates(Request $request, Helpers $helpers)
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

    public function importStockLines(Request $request, Helpers $helpers)
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
                $column = fgetcsv($fileD); // skips first row as header

                while (!feof($column)) {
                    $rowData[] = fgetcsv($column);
                }

                //delete template headers and lines
                // DB::table('template_header')->delete();
                // DB::table('template_lines')->delete();

                foreach ($rowData as $key => $row) {
                    //get Template no from header
                    // $item = TemplateHeader::firstOrCreate(
                    //     ['template_no' =>  $row[0]],
                    //     ['template_name' => $row[0], 'user_id' => $helpers->authenticatedUserId()],
                    // );

                    DB::table('spices_stock')->insert(
                        [
                            'item_code' => $row[2],
                            'description' => $row[1],
                            'quantity' => $row[2],
                            'entry_type' => 1
                        ]
                    );
                }
            });

            Toastr::success('Incoming Dry goods stocks uploaded successfully', 'Success');
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
            ->orderBy('template_lines.type', 'ASC')
            ->get();

        return view('spices.production-lines', compact('title', 'lines', 'helpers', 'batch_no'));
    }

    public function updateBatchItems(Request $request)
    {
        try {
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

                        //insert into Negative adjustments in stock entries
                        foreach ($request->item_array as $item) {
                            # code...

                            DB::table('spices_stock')->insert([
                                'item_code' => strtok($item, ':'),
                                'quantity' => -1 * abs((float)substr($item, strpos($item, ":") + 1)), // negative adjustment
                                'entry_type' => '2', //consumption
                                'user_id' => $helpers->authenticatedUserId(),
                            ]);
                        }
                    }
                );
            }

            Toastr::success("Action {$request->filter} batch no: {$request->item_name} completed successfully", 'Success');
            return redirect()->route('batches_list', $route_filter);
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back();
        }
    }
}
