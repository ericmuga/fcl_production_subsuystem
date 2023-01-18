<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProcessesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $processes =
            [
                [
                    'process_code' => 0,
                    'shortcode' => 'BP',
                    'process' => 'Behead Pig'
                ],
                [
                    'process_code' => 1,
                    'shortcode' => 'BS',
                    'process' => 'Behead Sow'
                ],
                [
                    'process_code' => 2,
                    'shortcode' => 'PB',
                    'process' => 'Breaking Pig, (Leg, Mdl, Shld)'
                ],
                [
                    'process_code' => 3,
                    'shortcode' => 'SB',
                    'process' => 'Breaking Sow into Leg,Mid,&Shd'
                ],
                [
                    'process_code' => 4,
                    'shortcode' => 'DL',
                    'process' => 'Debone Pork Leg'
                ],
                [
                    'process_code' => 5,
                    'shortcode' => 'DM',
                    'process' => 'Debone Pork Middle'
                ],
                [
                    'process_code' => 6,
                    'shortcode' => 'DS',
                    'process' => 'Debone Pork Shoulder'
                ],
                [
                    'process_code' => 7,
                    'shortcode' => 'DSW',
                    'process' => 'Debone Sow'
                ],
                [
                    'process_code' => 8,
                    'shortcode' => 'SL',
                    'process' => 'Slicing parts for slices, portions'
                ],
                [
                    'process_code' => 9,
                    'shortcode' => 'TR',
                    'process' => 'Trim & Roll'
                ],
                [
                    'process_code' => 10,
                    'shortcode' => 'FS',
                    'process' => 'Fat Stripping Rinds'
                ],
                [
                    'process_code' => 11,
                    'shortcode' => 'RPL',
                    'process' => 'Rolling Pork Legs'
                ],
                [
                    'process_code' => 12,
                    'shortcode' => 'RPS',
                    'process' => 'Rolling Pork Shoulders'
                ],
                [
                    'process_code' => 13,
                    'shortcode' => 'BN',
                    'process' => 'Bones'
                ],

            ];

        DB::table('processes')->insert($processes);
    }
}
