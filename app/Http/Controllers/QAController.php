<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Illuminate\Support\Facades\DB;

class QAController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        $title = 'QA Dashboard';

        // Today's sent transfers from QA (location 4450)
        $todays_sent = DB::table('idt_transfers')
            ->whereDate('created_at', today())
            ->where('transfer_from', '4450')
            ->select(
                DB::raw('COUNT(*) as total_transfers'),
                DB::raw('SUM(total_pieces) as total_pieces'),
                DB::raw('SUM(total_weight) as total_weight')
            )->first();

        // Today's received transfers into QA (location_code 4450)
        $todays_received = DB::table('idt_transfers')
            ->whereDate('created_at', today())
            ->where('location_code', '4450')
            ->select(
                DB::raw('COUNT(received_by) as total_transfers'),
                DB::raw('SUM(receiver_total_pieces) as total_pieces'),
                DB::raw('SUM(receiver_total_weight) as total_weight')
            )->first();

        // Pending approvals entries where QA is involved (either from or to QA)
        $pending_approvals = DB::table('idt_transfers')
            ->where(function ($q) {
                $q->where('location_code', '4450')
                  ->orWhere('transfer_from', '4450');
            })
            ->where('requires_approval', 1)
            ->whereNull('approved')
            ->select(
                DB::raw('COUNT(*) as total_transfers'),
                DB::raw('SUM(total_pieces) as total_pieces'),
                DB::raw('SUM(total_weight) as total_weight')
            )->first();

        return view('qa.dashboard', compact('title', 'todays_sent', 'todays_received', 'pending_approvals'));
    }

    public function issue()
    {
        // Thin wrapper around generic IDT issue screen
        return redirect()->route('list_issued_idt', ['from_location' => '4450']);
    }

    public function receive()
    {
        // By default show all transfers coming into QA from any location
        // Use generic IDT receive screen; from_location is left open via query string
        return redirect()->route('list_receive', ['to_location' => '4450']);
    }

    public function idtReport($filter = null, $filter2 = null)
    {
        $title = 'QA IDT Transfer Report';

        // Determine date filter
        $days_filter = 7;
        if ($filter && is_numeric($filter)) {
            $days_filter = (int)$filter;
        }

        return redirect()->route('idt_history', [
            'filter'        => $filter,
            'filter2'       => $filter2,
            'from_location' => '4450', // QA
            'to_location'   => '4450',
        ]);
    }
}
