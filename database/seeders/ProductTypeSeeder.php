<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = [
            [
            'code' => '1',
            'description' => 'Main Product',
            ],
            [
            'code' => '2',
            'description' => 'By Product',
            ],
            [
            'code' => '3',
            'description' => 'Intake',
            ],
        ];

        DB::table('product_types')->insert($types);
    }
}
