<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ButcheryStockController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check');
    }

    public function index()
    {
        $title = "dashboard";

        return view('stocks.dashboard', compact('title'));
    }

    public function stocksTransactions()
    {
        $title = "transactions";

        $transactions = DB::table('transactions')
            // ->whereDate('created_at', today())
            ->get();

        return view('stocks.transactions', compact('title', 'transactions'));
    }
}
