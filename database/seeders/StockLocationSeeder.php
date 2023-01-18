<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StockLocationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data =
            [
                [
                    'description' => 'Sale'
                ],
                [
                    'description' => 'Transfer'
                ],
                [
                    'description' => 'Positive adj'
                ],
                [
                    'description' => 'Negative adj'
                ],
                [
                    'description' => 'Production'
                ],
            ];

        DB::table('entry_types')->insert($data);
    }
}
