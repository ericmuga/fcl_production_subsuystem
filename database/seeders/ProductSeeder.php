<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    // public function run()
    // {
    //     $path = 'app/developer_docs/countries.sql';
    //     $path = 'C:/Apache24/htdocs/calibra/products_script.sql';
    //     DB::unprepared(file_get_contents($path));
    //     $this->command->info('Products table seeded!');
    // }
    public function run()
    {
        /* not tested hence commented out in seeder run */

        // Eloquent::unguard();

        $path = public_path('sql/products_script.sql');
        DB::unprepared(file_get_contents($path));
        $this->command->info('Products table seeded!');
    }
}
