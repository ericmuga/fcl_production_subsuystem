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

    public function choppingSaveBatch(Request $request)
    {
        dd($request->all());
    }
}
