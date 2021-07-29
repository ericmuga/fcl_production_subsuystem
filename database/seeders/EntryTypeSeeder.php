<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EntryTypeSeeder extends Seeder
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
                    'entry_code' => '1',
                    'description' => 'Sale'
                ],
                [
                    'entry_code' => '2',
                    'description' => 'Transfer'
                ],
                [
                    'entry_code' => '3',
                    'description' => 'Positive adj'
                ],
                [
                    'entry_code' => '4',
                    'description' => 'Negative adj'
                ],
                [
                    'entry_code' => '5',
                    'description' => 'Production'
                ],
            ];

        DB::table('entry_types')->insert($types);
    }
}
