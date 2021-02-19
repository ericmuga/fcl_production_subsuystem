<?php

namespace App\Imports;

use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Concerns\ToModel;

class ReceiptsImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $slaughter_date = Session::get('slaughter_date');

        DB::table('receipts')->insert([
            'enrolment_no' => $row[0],
            'vendor_tag' => $row[1],
            'receipt_no' => $row[2],
            'vendor_no' => $row[3],
            'vendor_name' => $row[4],
            'receipt_date' => $row[5],
            'item_code' => $row[6],
            'description' => $row[7],
            'received_qty' => $row[8],
            'user_id' => Auth::id(),
            'slaughter_date' => $slaughter_date,
        ]);
    }
}
