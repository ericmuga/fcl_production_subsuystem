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
                'process' => 'Behead Pig'
            ],
            [
                'process_code' => 1,
                'process' => 'Behead Sow'
            ],
            [
                'process_code' => 2,
                'process' => 'Breaking Pig, (Leg, Mdl, Shld)'
            ],
            [
                'process_code' => 3,
                'process' => 'Breaking Sow into Leg,Mid,&Shd'
            ],
            [
                'process_code' => 4,
                'process' => 'Debone Pork Leg'
            ],
            [
                'process_code' => 5,
                'process' => 'Debone Pork Middle'
            ],
            [
                'process_code' => 6,
                'process' => 'Debone Pork Shoulder'
            ],
            [
                'process_code' => 7,
                'process' => 'Debone Sow'
            ],
            [
                'process_code' => 8,
                'process' => 'Slicing parts for slices, portions'
            ],
            [
                'process_code' => 9,
                'process' => 'Trim & Roll'
            ],
            [
                'process_code' => 10,
                'process' => 'Fat Stripping Rinds'
            ],
            [
                'process_code' => 11,
                'process' => 'Rolling Pork Legs'
            ],
            [
                'process_code' => 12,
                'process' => 'Rolling Pork Shoulders'
            ],
            [
                'process_code' => 13,
                'process' => 'Bones'
            ],

        ];

        DB::table('processes')->insert($processes);
    }
}
