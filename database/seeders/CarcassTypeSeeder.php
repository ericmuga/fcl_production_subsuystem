<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarcassTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types =
        [
            [
                'code' => 'G0110',
                'description' => 'Pig, Carcass',
            ],
            [
                'code' => 'G0111',
                'description' => 'Sow, Carcass',
            ],
            [
                'code' => 'G0113',
                'description' => 'Suckling, Carcass',
            ],

        ];

        DB::table('carcass_types')->insert($types);
    }
}
