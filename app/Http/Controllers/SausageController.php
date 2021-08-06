<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mockery\Matcher\Type;

class SausageController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check');
    }

    public function index()
    {
        $title = "Dashboard";
        // $this->readBarcodes();

        return view('sausage.dashboard', compact('title'));
    }

    public function productionEntries()
    {
        $title = "Todays-Entries";

        $entries = DB::table('sausage_entries')
            ->where('created_at', today())
            ->get();

        return view('sausage.entries', compact('entries', 'title'));
    }

    public function itemsList()
    {
        $title = "Items-List";
        $category = "JF-SAUSAGE";

        $items = Cache::remember('items_list', now()->addMinutes(480), function () use ($category) {
            return DB::table('items')
                ->where('category', $category)
                ->get();
        });

        return view('sausage.items', compact('title', 'category', 'items'));
    }

    public function readBarcodes()
    {
        $fileD = fopen("C:\\Users\\EKaranja\\OneDrive - Farmers Choice Limited\\Documents\\DataMax\\log\\codeLog_20210805.txt", "r");
        // $column = fgetcsv($fileD); // skips first row as header

        while (!feof($fileD)) {
            $rowData[] = fgetcsv($fileD);
        }

        $data = [];
        foreach ($rowData as $key => $row) {
            // $origin_timestamps = substr($row, -1);

            // array_push($data, $row);
            dd($row);
        }


        // $data = [
        //     '13:48:04.611 6161102030556',
        //     '13:48:04.667 6161102030556',
        //     '13:48:04.727 6161102030556',
        //     '13:48:06.460 6161102030556',
        //     '13:48:06.513 6161102030556',
        //     '13:48:12.311 6161102030556',
        //     '13:48:12.415 6161102030556',
        //     '13:48:16.420 6161102030556',
        //     '13:48:20.151 6161102030556',
        //     '13:48:20.232 6161102030556',
        //     '13:48:20.292 6161102030556',
        //     '13:48:24.043 6161102030556',
        //     '13:48:28.293 6161102030556',
        //     '13:48:30.227 6161102030556',
        //     '13:48:31.830 6161102030556',
        //     '13:48:32.061 6161102030556',
        //     '13:48:36.073 6161102030556',
        //     '13:48:36.121 6161102030556',
        //     '13:48:36.172 6161102030556',
        //     '13:48:37.793 6161102030556',
        //     '13:48:45.798 6161102030556',
        //     '13:48:53.788 6161102030556',
        //     '13:48:57.493 6161102030556',
        //     '13:48:57.543 6161102030556',
        //     '13:48:59.670 6161102030556',
        //     '13:48:59.719 6161102030556',
        //     '13:48:59.764 6161102030556',
        //     '13:49:01.663 6161102030556',
        //     '13:49:01.713 6161102030556',
        //     '13:49:03.573 6161102030556',
        //     '13:49:03.628 6161102030556',
        //     '13:49:03.673 6161102030556',
        //     '13:49:05.772 6161102030556',
        //     '13:49:05.829 6161102030556',
        //     '13:49:05.874 6161102030556',
        //     '13:49:09.576 6161102030556',
        //     '13:49:13.266 6161102030556',
        //     '13:49:13.365 6161102030556',
        //     '13:49:21.607 6161102030556',
        //     '13:49:23.472 6161102030556',
        //     '13:49:23.520 6161102030556',
        //     '13:49:23.521 6161102030556',
        //     '13:49:23.567 6161102030556',
        //     '13:49:27.262 6161102030556',
        //     '13:49:27.313 6161102030556',
        //     '13:49:27.314 6161102030556',
        //     '13:49:27.387 6161102030556',
        //     '13:49:29.253 6161102030556',
        //     '13:49:29.392 6161102030556',
        //     '13:49:50.685 6161102030556',
        //     '13:49:50.768 6161102030556',
        //     '13:49:50.899 6161102030556',
        //     '13:49:53.815 6161102030556',
        //     '13:49:53.933 6161102030556',
        //     '13:49:57.730 6161102030556',
        //     '13:49:59.366 6161102030556',
        //     '13:49:59.412 6161102030556',
        //     '13:49:59.458 6161102030556',
        //     '13:50:05.558 6161102030556',
        //     '13:50:05.610 6161102030556'
        // ];

        // // save into order_items
        // for ($count = 0; $count < count($data); $count++) {

        //     $origin_timestamp = substr($data[$count], 0, 12);
        //     $barcode = substr($data[$count], -13);

        //     DB::table('sausage_entries')->insert([
        //         'origin_timestamp' => $origin_timestamp,
        //         'barcode' => $barcode,
        //     ]);
        // }


    }
}
