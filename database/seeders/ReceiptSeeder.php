<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReceiptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $receipts =
        // [
        //     [
        //         'enrolment_no' => 'FRT-0000005307',
        //         'vendor_tag' => '842A',
        //         'receipt_no' => 'FRT-0000005307',
        //         'vendor_no' => 'PF03941',
        //         'vendor_name' => 'Thomas Kamau',
        //         'item_code' => 'G0101',
        //         'description' => 'Pig, Livestock',
        //         'received_qty' => '4',
        //         'slaughter_date' => Carbon::yesterday(),
        //         'user_id' => Auth::id(),
        //     ],
        //     [
        //         'enrolment_no' => 'FRT-0000005308',
        //         'vendor_tag' => '9418',
        //         'receipt_no' => 'FRT-0000005308',
        //         'vendor_no' => 'PF12243',
        //         'vendor_name' => 'Joshua Mwiri',
        //         'item_code' => 'G0101',
        //         'description' => 'Pig, Livestock',
        //         'received_qty' => '6',
        //         'slaughter_date' => Carbon::yesterday(),
        //     ],

        // ];

        // DB::table('receipts')->insert($receipts);
    }
}
