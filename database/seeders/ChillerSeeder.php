<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ChillerSeeder extends Seeder
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
                    'chiller_code' => 'C',
                    'location_code' => '1570'
                ],
                [
                    'chiller_code' => 'D',
                    'location_code' => '1570'
                ],
            ];

        DB::table('chillers')->insert($data);
    }
}
