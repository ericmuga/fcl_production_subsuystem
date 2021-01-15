<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScaleConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $configs =
        [
            [
                'scale' => 'Scale 1',
                'comport' => 'com4',
                'baudrate' => '9600',
                'tareweight' => '2.4',
                'section' => 'slaughter',
            ],
            [
                'scale' => 'Scale 1',
                'comport' => 'com5',
                'baudrate' => '9600',
                'tareweight' => '2.4',
                'section' => 'butchery',
            ],
            [
                'scale' => 'Scale 2',
                'comport' => 'com6',
                'baudrate' => '9600',
                'tareweight' => '7.5',
                'section' => 'butchery',
            ],
        ];

        DB::table('scale_configs')->insert($configs);
    }
}
