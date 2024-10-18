<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ItemsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['code' => 'J31010101', 'barcode' => '6161102031706', 'description' => 'Pork Chipolatas 200gms', 'category' => NULL, 'unit_of_measure' => 'PC', 'qty_per_unit_of_measure' => 0.2, 'unit_count_per_crate' => 120, 'blocked' => 0],
            ['code' => 'J31010102', 'barcode' => '6161102033472', 'description' => 'Pork Chipolatas 1kg', 'category' => NULL, 'unit_of_measure' => 'PC', 'qty_per_unit_of_measure' => 1, 'unit_count_per_crate' => 25, 'blocked' => 0],
            ['code' => 'J31010201', 'barcode' => '6161102031683', 'description' => 'Premium Pork Sausages 400gms', 'category' => NULL, 'unit_of_measure' => 'PC', 'qty_per_unit_of_measure' => 0.4, 'unit_count_per_crate' => 60, 'blocked' => 0],
            ['code' => 'J31010301', 'barcode' => '6161102032000', 'description' => 'Meaty Pork Sausage 400g- Xpt', 'category' => NULL, 'unit_of_measure' => 'PC', 'qty_per_unit_of_measure' => 0.4, 'unit_count_per_crate' => 60, 'blocked' => 0],
            ['code' => 'J31010302', 'barcode' => '6161102032901', 'description' => 'Meaty Pork Sausages 1kg', 'category' => NULL, 'unit_of_measure' => 'PC', 'qty_per_unit_of_measure' => 1, 'unit_count_per_crate' => 25, 'blocked' => 0],
            ['code' => 'J31010401', 'barcode' => '6161102031980', 'description' => 'Spicy Pork Sausages 400gms', 'category' => NULL, 'unit_of_measure' => 'PC', 'qty_per_unit_of_measure' => 0.4, 'unit_count_per_crate' => 60, 'blocked' => 0],
            ['code' => 'J31010402', 'barcode' => '6161102030495', 'description' => 'Pork & Herb Sausages 400gms', 'category' => NULL, 'unit_of_measure' => 'PC', 'qty_per_unit_of_measure' => 0.4, 'unit_count_per_crate' => 25, 'blocked' => 0],
            ['code' => 'J31010420', 'barcode' => '6161102032536', 'description' => 'Spicy Pork Sausages V/P 1kg', 'category' => NULL, 'unit_of_measure' => 'PC', 'qty_per_unit_of_measure' => 1, 'unit_count_per_crate' => 25, 'blocked' => 0],
            ['code' => 'J31010502', 'barcode' => '6161102030433', 'description' => 'Value Pack Pork Sausages 1kg', 'category' => NULL, 'unit_of_measure' => 'PC', 'qty_per_unit_of_measure' => 1, 'unit_count_per_crate' => 25, 'blocked' => 0],
            ['code' => 'J31010503', 'barcode' => '6161102033496', 'description' => 'Pork Sausage Meat 3kgs', 'category' => NULL, 'unit_of_measure' => 'PC', 'qty_per_unit_of_measure' => 3, 'unit_count_per_crate' => 6, 'blocked' => 0],
            ['code' => 'J31021003', 'barcode' => '6161102031737', 'description' => 'Streaky Bacon 1kg', 'category' => NULL, 'unit_of_measure' => 'PC', 'qty_per_unit_of_measure' => 1, 'unit_count_per_crate' => 18, 'blocked' => 0],
            ['code' => 'J31021006', 'barcode' => '6161102030983', 'description' => 'Premium Streaky Bacon 400gms', 'category' => NULL, 'unit_of_measure' => 'PC', 'qty_per_unit_of_measure' => 0.4, 'unit_count_per_crate' => 50, 'blocked' => 0],
            ['code' => 'J31021005', 'barcode' => '6161102030174', 'description' => 'Streaky Bacon 100gms', 'category' => NULL, 'unit_of_measure' => 'PC', 'qty_per_unit_of_measure' => 0.1, 'unit_count_per_crate' => 100, 'blocked' => 0],
        ];

        DB::table('items')->insert($data);
    }
}
