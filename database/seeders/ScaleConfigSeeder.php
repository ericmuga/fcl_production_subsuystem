<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
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
                    'comport' => 'COM4',
                    'baudrate' => '9600',
                    'tareweight' => '2.4',
                    'section' => 'slaughter',
                    'user_id' => 1,
                ],
                [
                    'scale' => 'Scale 1',
                    'comport' => 'COM5',
                    'baudrate' => '9600',
                    'tareweight' => '2.4',
                    'section' => 'butchery',
                    'user_id' => 1,
                ],
                [
                    'scale' => 'Scale 2',
                    'comport' => 'COM6',
                    'baudrate' => '9600',
                    'tareweight' => '7.5',
                    'section' => 'butchery',
                    'user_id' => 1,
                ],
                [
                    'scale' => 'Scale 3',
                    'comport' => 'COM7',
                    'baudrate' => '9600',
                    'tareweight' => '1.8',
                    'section' => 'butchery',
                    'user_id' => 1,
                ],
            ];

        DB::table('scale_configs')->insert($configs);
    }
}
