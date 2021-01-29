<?php
namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class Helpers
{
    public function validateUserPassword()
    {
        if (Hash::check("param1", "param2")) {
            //add logic here
           }

        //    param1 - user password that has been entered on the form
        //    param2 - old password hash stored in database

    }

    public function dateToHumanFormat($date)
    {
        return date("F jS, Y", strtotime($date));
    }

    public function getProductName($code)
    {
        return Product::where('code', $code)->value('description');
    }

    public function getButcheryDate(){
        $proposed_butchery_date = Carbon::yesterday();
        if ( SlaughterData::whereDate('created_at', '=', Carbon::yesterday())->exists() ) {
            // yesterday is valid
            return $proposed_butchery_date = Carbon::yesterday();

        }
        elseif ( SlaughterData::whereDate('created_at', '=', Carbon::yesterday()->subDays(1))->exists() ) {
            # yesterday minus 1 day is valid
            return $proposed_butchery_date = Carbon::yesterday()->subDays(1);

        }
        elseif ( SlaughterData::whereDate('created_at', '=', Carbon::yesterday()->subDays(2))->exists() ) {
            # yesterday minus 2 day is valid
            return $proposed_butchery_date = Carbon::yesterday()->subDays(2);

        }
        elseif ( SlaughterData::whereDate('created_at', '=', Carbon::yesterday()->subDays(3))->exists() ) {
            # yesterday minus 2 day is valid
            return $proposed_butchery_date = Carbon::yesterday()->subDays(3);

        }
        return $proposed_butchery_date;
    }

    public function getInputData()
    {
        $input_count = BeheadingData::whereDate('created_at', Carbon::today())
            ->sum('no_of_carcass');
        return $input_count;

    }

    public function getOutputData()
    {
        $output_legs = ButcheryData::whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1100')
            ->sum('no_of_items');

        $output_middles = ButcheryData::whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1101')
            ->sum('no_of_items');

        $output_shoulders = ButcheryData::whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1102')
            ->sum('no_of_items');

        $output_count = ['output_legs' => $output_legs, 'output_middles' => $output_middles, 'output_shoulders' => $output_shoulders];
        return $output_count;

    }

    public function getProcessCode($process_name)
    {
        $process_code = ProductionProcess::where('process', $process_name)
            ->orWhere('process', 'like', '%' . $process_name . '%')
            ->value('process_code');

        return $process_code;

    }

}
