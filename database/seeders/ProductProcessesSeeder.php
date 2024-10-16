<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductProcessesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {
        $product_processes = [
            ['product_code' => 'G1033', 'process_code' => 0, 'product_type' => 1],
            ['product_code' => 'G1093', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1100', 'process_code' => 2, 'product_type' => 1],
            ['product_code' => 'G1101', 'process_code' => 2, 'product_type' => 1],
            ['product_code' => 'G1102', 'process_code' => 2, 'product_type' => 1],
            ['product_code' => 'G1119', 'process_code' => 4, 'product_type' => 1],
            ['product_code' => 'G1119', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1121', 'process_code' => 15, 'product_type' => 3],
            ['product_code' => 'G1121', 'process_code' => 4, 'product_type' => 1],
            ['product_code' => 'G1121', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1122', 'process_code' => 4, 'product_type' => 1],
            ['product_code' => 'G1122', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1123', 'process_code' => 4, 'product_type' => 1],
            ['product_code' => 'G1124B', 'process_code' => 4, 'product_type' => 1],
            ['product_code' => 'G1125', 'process_code' => 4, 'product_type' => 1],
            ['product_code' => 'G1126', 'process_code' => 4, 'product_type' => 1],
            ['product_code' => 'G1127', 'process_code' => 4, 'product_type' => 1],
            ['product_code' => 'G1128', 'process_code' => 4, 'product_type' => 1],
            ['product_code' => 'G1128', 'process_code' => 11, 'product_type' => 3],
            ['product_code' => 'G1131', 'process_code' => 4, 'product_type' => 1],
            ['product_code' => 'G1131', 'process_code' => 11, 'product_type' => 1],
            ['product_code' => 'G1132', 'process_code' => 4, 'product_type' => 1],
            ['product_code' => 'G1137', 'process_code' => 4, 'product_type' => 1],
            ['product_code' => 'G1149', 'process_code' => 8, 'product_type' => 1],
            ['product_code' => 'G1159', 'process_code' => 6, 'product_type' => 1],
            ['product_code' => 'G1159', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1160', 'process_code' => 6, 'product_type' => 1],
            ['product_code' => 'G1160', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1160', 'process_code' => 15, 'product_type' => 3],
            ['product_code' => 'G1161', 'process_code' => 6, 'product_type' => 1],
            ['product_code' => 'G1161', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1162', 'process_code' => 6, 'product_type' => 1],
            ['product_code' => 'G1164', 'process_code' => 6, 'product_type' => 1],
            ['product_code' => 'G1165', 'process_code' => 6, 'product_type' => 1],
            ['product_code' => 'G1165', 'process_code' => 12, 'product_type' => 3],
            ['product_code' => 'G1166', 'process_code' => 6, 'product_type' => 1],
            ['product_code' => 'G1166', 'process_code' => 12, 'product_type' => 3],
            ['product_code' => 'G1168', 'process_code' => 6, 'product_type' => 2],
            ['product_code' => 'G1168', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1169', 'process_code' => 6, 'product_type' => 1],
            ['product_code' => 'G1176', 'process_code' => 8, 'product_type' => 1],
            ['product_code' => 'G1189', 'process_code' => 5, 'product_type' => 1],
            ['product_code' => 'G1189', 'process_code' => 8, 'product_type' => 1],
            ['product_code' => 'G1191', 'process_code' => 8, 'product_type' => 2],
            ['product_code' => 'G1191', 'process_code' => 5, 'product_type' => 1],
            ['product_code' => 'G1193', 'process_code' => 5, 'product_type' => 1],
            ['product_code' => 'G1194', 'process_code' => 5, 'product_type' => 2],
            ['product_code' => 'G1194', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1195', 'process_code' => 5, 'product_type' => 2],
            ['product_code' => 'G1195', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1195', 'process_code' => 8, 'product_type' => 2],
            ['product_code' => 'G1196', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1196', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1196', 'process_code' => 5, 'product_type' => 1],
            ['product_code' => 'G1197', 'process_code' => 5, 'product_type' => 1],
            ['product_code' => 'G1197', 'process_code' => 8, 'product_type' => 1],
            ['product_code' => 'G1197', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1197', 'process_code' => 15, 'product_type' => 3],
            ['product_code' => 'G1198', 'process_code' => 5, 'product_type' => 1],
            ['product_code' => 'G1198', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1199', 'process_code' => 5, 'product_type' => 1],
            ['product_code' => 'G1204', 'process_code' => 5, 'product_type' => 1],
            ['product_code' => 'G1204', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1208', 'process_code' => 8, 'product_type' => 1],
            ['product_code' => 'G1211', 'process_code' => 8, 'product_type' => 1],
            ['product_code' => 'G1221', 'process_code' => 5, 'product_type' => 2],
            ['product_code' => 'G1221', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1222', 'process_code' => 8, 'product_type' => 2],
            ['product_code' => 'G1222', 'process_code' => 5, 'product_type' => 2],
            ['product_code' => 'G1223', 'process_code' => 5, 'product_type' => 2],
            ['product_code' => 'G1224', 'process_code' => 5, 'product_type' => 2],
            ['product_code' => 'G1224', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1225', 'process_code' => 8, 'product_type' => 1],
            ['product_code' => 'G1225', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1226', 'process_code' => 8, 'product_type' => 2],
            ['product_code' => 'G1227', 'process_code' => 8, 'product_type' => 3],
            ['product_code' => 'G1228', 'process_code' => 8, 'product_type' => 2],
            ['product_code' => 'G1230', 'process_code' => 8, 'product_type' => 3],
        ];

        DB::table('product_processes')->insert($product_processes);
    }
}
