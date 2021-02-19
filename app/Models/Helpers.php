<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

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

    public function getButcheryDate()
    {
        $proposed_butchery_date = Carbon::yesterday();
        if (SlaughterData::whereDate('created_at', '=', Carbon::yesterday())->exists()) {
            // yesterday is valid
            return $proposed_butchery_date = Carbon::yesterday();
        } elseif (SlaughterData::whereDate('created_at', '=', Carbon::yesterday()->subDays(1))->exists()) {
            # yesterday minus 1 day is valid
            return $proposed_butchery_date = Carbon::yesterday()->subDays(1);
        } elseif (SlaughterData::whereDate('created_at', '=', Carbon::yesterday()->subDays(2))->exists()) {
            # yesterday minus 2 day is valid
            return $proposed_butchery_date = Carbon::yesterday()->subDays(2);
        } elseif (SlaughterData::whereDate('created_at', '=', Carbon::yesterday()->subDays(3))->exists()) {
            # yesterday minus 3 day is valid
            return $proposed_butchery_date = Carbon::yesterday()->subDays(3);
        }
        return $proposed_butchery_date;
    }

    public function getInputData()
    {
        $input_count = DB::table('beheading_data')
            ->whereDate('created_at', Carbon::today())
            ->sum('no_of_carcass');
        return $input_count;
    }

    public function getOutputData()
    {
        $output_legs = DB::table('butchery_data')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1100')
            ->sum('no_of_items');

        $output_middles = DB::table('butchery_data')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1101')
            ->sum('no_of_items');

        $output_shoulders = DB::table('butchery_data')
            ->whereDate('created_at', Carbon::today())
            ->where('item_code', 'G1102')
            ->sum('no_of_items');

        $output_count = ['output_legs' => $output_legs, 'output_middles' => $output_middles, 'output_shoulders' => $output_shoulders];
        return $output_count;
    }

    public function getProcessCode($process_name)
    {
        $process_code = ProductionProcess::where('process', $process_name)
            // ->orWhere('process', 'like', '%' . $process_name . '%')
            ->value('process_code');

        return $process_code;
    }

    public function getProductCode($product_name)
    {
        $product_code = Product::where('description', $product_name)
            // ->orWhere('description', 'like', '%' . $product_name . '%')
            ->value('code');

        return $product_code;
    }

    public function getReadScaleApiServiceUrl()
    {
        $ip = \Request::getClientIp(true);

        if ($ip == '::1') {
            $ip = '127.0.0.1';
        }
        return $url = 'http://' . $ip . ':3000/api/get-scale-reading';

        // return $url = config('app.read_scale_api_url');
    }

    public function getComportListServiceUrl()
    {
        $ip = \Request::getClientIp(true);

        if ($ip == '::1') {
            $ip = '127.0.0.1';
        }
        return $url = 'http://' . $ip . ':3000/api/get-comport-list';

        // return $url = config('app.list_comport_api_url');
    }

    public function get_scale_read($comport)
    {
        $curl = curl_init();
        $url = $this->getReadScaleApiServiceUrl();
        $full_url = $url . '/' . $comport;

        curl_setopt_array($curl, array(
            CURLOPT_URL => $full_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set Here Your Requesred Headers
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function get_comport_list()
    {
        $curl = curl_init();

        $url = $this->getComportListServiceUrl();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_TIMEOUT => 30000,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                // Set Here Your Requesred Headers
                'Content-Type: application/json',
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
}
