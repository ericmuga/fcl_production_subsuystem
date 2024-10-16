<?php

namespace Database\Seeders;

use CarcassTypeTable;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            // UserSeeder::class,
            // ReceiptSeeder::class,
            //items
            ScaleConfigSeeder::class,
            CarcassTypeSeeder::class,
            ProductSeeder::class,
            ProcessesSeeder::class,
            ProductTypeSeeder::class,
            ProductProcessesSeeder::class,
            EntryTypeSeeder::class,
        ]);
    }
}
