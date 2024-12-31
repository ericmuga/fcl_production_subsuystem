<?php

namespace App\Http\Controllers;

use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


class PetfoodController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard(Request $request)
    {
        $title = "Petfood Dashboard";

        $total_issued_pieces = DB::table('idt_transfers')->where('transfer_from', '3035')->whereDate('created_at', today())->sum('total_pieces');

        $total_issued_weight = DB::table('idt_transfers')->where('transfer_from', '3035')->whereDate('created_at', today())->sum('total_weight');

        $total_received_weight = DB::table('idt_transfers')->where('location_code', '3035')->whereDate('created_at', today())->sum('total_weight');

        $todays_transaction_count = DB::table('idt_transfers')->where('transfer_from', '3035')->orWhere('location_code', '3035')->whereDate('created_at', today())->count();
       
        return view('petfood.dashboard', compact('title', 'total_issued_pieces', 'total_issued_weight', 'total_received_weight', 'todays_transaction_count'));
    }
}