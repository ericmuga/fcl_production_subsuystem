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

                foreach ($rowData as $key => $row) {
                    //get Template no from header
                    $template = TemplateHeader::firstOrCreate(
                        ['template_no' =>  $row[0]],
                        ['template_name' => $row[0], 'user_id' => $helpers->authenticatedUserId()],
                    );

                    DB::table('template_lines')->insert(
                        [
                            'template_no' => $template->template_no,
                            'item_no' => $row[1],
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

    public function BatchLists($filter = null)
    {
        $title = "Production Lines";

        $status = $filter;

        return view('spices.production-lines', compact('title', 'status'));
    }
}
