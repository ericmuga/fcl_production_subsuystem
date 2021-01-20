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
                'user_id' => 1,
            ],
            [
                'code' => 'G0111',
                'description' => 'Sow, Carcass',
                'user_id' => 1,
            ],
            [
                'code' => 'G0113',
                'description' => 'Suckling, Carcass',
                'user_id' => 1,
            ],

        ];

        DB::table('carcass_types')->insert($types);
    }
}
