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

    public function getProductId($product_code)
    {
        return DB::table('products')->where('code', $product_code)->value('id');
    }

    public function run()
    {
        $product_processes = [
            [
                'product_id' => $this->getProductId("G0110"),
                'process_code' => '0',
            ],
            [
                'product_id' => $this->getProductId("G0111"),
                'process_code' => '1',
            ],
            [
                'product_id' => $this->getProductId("G1030"),
                'process_code' => '2',
            ],
            [
                'product_id' => $this->getProductId("G1031"),
                'process_code' => '3',
            ],
            [
                'product_id' => $this->getProductId("G1032"),
                'process_code' => '0',
            ],
            [
                'product_id' => $this->getProductId("G1033"),
                'process_code' => '0',
            ],
            [
                'product_id' => $this->getProductId("G1093"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1100"),
                'process_code' => '2',
            ],
            [
                'product_id' => $this->getProductId("G1101"),
                'process_code' => '2',
            ],
            [
                'product_id' => $this->getProductId("G1102"),
                'process_code' => '2',
            ],
            # 10
            [
                'product_id' => $this->getProductId("G1119"),
                'process_code' => '4',
            ],
            [
                'product_id' => $this->getProductId("G1121"),
                'process_code' => '4',
            ],
            [
                'product_id' => $this->getProductId("G1121"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1122"),
                'process_code' => '4',
            ],
            [
                'product_id' => $this->getProductId("G1122"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1123"),
                'process_code' => '4',
            ],
            // [
            //     'product_id' => $this->getProductId("G1124"),
            //     'process_code' => 'N/A',
            // ],
            [
                'product_id' => $this->getProductId("G1124B"),
                'process_code' => '4',
            ],
            [
                'product_id' => $this->getProductId("G1125"),
                'process_code' => '4',
            ],
            [
                'product_id' => $this->getProductId("G1126"),
                'process_code' => '4',
            ],
            [
                'product_id' => $this->getProductId("G1127"),
                'process_code' => '4',
            ],
            [
                'product_id' => $this->getProductId("G1128"),
                'process_code' => '4',
            ],
            [
                'product_id' => $this->getProductId("G1128"),
                'process_code' => '11',
            ],
            # 20
            [
                'product_id' => $this->getProductId("G1131"),
                'process_code' => '4',
            ],
            [
                'product_id' => $this->getProductId("G1131"),
                'process_code' => '11',
            ],
            [
                'product_id' => $this->getProductId("G1132"),
                'process_code' => '4',
            ],
            // [
            //     'product_id' => $this->getProductId("G1137"),
            //     'process_code' => 'N/A',
            // ],
            [
                'product_id' => $this->getProductId("G1137B"),
                'process_code' => '4',
            ],
            [
                'product_id' => $this->getProductId("G1149"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1159"),
                'process_code' => '6',
            ],
            [
                'product_id' => $this->getProductId("G1159"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1160"),
                'process_code' => '6',
            ],
            [
                'product_id' => $this->getProductId("G1160"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1161"),
                'process_code' => '6',
            ],
            [
                'product_id' => $this->getProductId("G1161"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1162"),
                'process_code' => '6',
            ],
            [
                'product_id' => $this->getProductId("G1164"),
                'process_code' => '6',
            ],
            # 30
            [
                'product_id' => $this->getProductId("G1165"),
                'process_code' => '6',
            ],
            [
                'product_id' => $this->getProductId("G1165"),
                'process_code' => '12',
            ],
            [
                'product_id' => $this->getProductId("G1166"),
                'process_code' => '6',
            ],
            [
                'product_id' => $this->getProductId("G1166"),
                'process_code' => '12',
            ],
            [
                'product_id' => $this->getProductId("G1168"),
                'process_code' => '6',
            ],
            [
                'product_id' => $this->getProductId("G1168"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1169"),
                'process_code' => '6',
            ],
            [
                'product_id' => $this->getProductId("G1176"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1189"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1191"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1193"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1194"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1194"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1195"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1195"),
                'process_code' => '8',
            ],
            # 40
            [
                'product_id' => $this->getProductId("G1196"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1197"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1198"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1198"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1199"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1204"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1204"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1208"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1211"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1221"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1222"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1223"),
                'process_code' => '5',
            ],

            # 50
            [
                'product_id' => $this->getProductId("G1224"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1225"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1227"),
                'process_code' => '6',
            ],
            [
                'product_id' => $this->getProductId("G1228"),
                'process_code' => '0',
            ],
            [
                'product_id' => $this->getProductId("G1229"),
                'process_code' => '2',
            ],
            [
                'product_id' => $this->getProductId("G1229"),
                'process_code' => '4',
            ],
            [
                'product_id' => $this->getProductId("G1229"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1229"),
                'process_code' => '6',
            ],
            [
                'product_id' => $this->getProductId("G1229"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1230"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1230"),
                'process_code' => '6',
            ],
            [
                'product_id' => $this->getProductId("G1230"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1231"),
                'process_code' => '3',
            ],
            [
                'product_id' => $this->getProductId("G1231"),
                'process_code' => '4',
            ],
            [
                'product_id' => $this->getProductId("G1231"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1231"),
                'process_code' => '6',
            ],
            [
                'product_id' => $this->getProductId("G1231"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1232"),
                'process_code' => '3',
            ],
            [
                'product_id' => $this->getProductId("G1232"),
                'process_code' => '10',
            ],
            [
                'product_id' => $this->getProductId("G1233"),
                'process_code' => '3',
            ],
            [
                'product_id' => $this->getProductId("G1233"),
                'process_code' => '10',
            ],
            [
                'product_id' => $this->getProductId("G1234"),
                'process_code' => '0',
            ],

            # 60
            [
                'product_id' => $this->getProductId("G1235"),
                'process_code' => '1',
            ],
            [
                'product_id' => $this->getProductId("G1235"),
                'process_code' => '2',
            ],
            [
                'product_id' => $this->getProductId("G1236"),
                'process_code' => '2',
            ],
            [
                'product_id' => $this->getProductId("G1236"),
                'process_code' => '8',
            ],
            // [
            //     'product_id' => $this->getProductId("G1237"),
            //     'process_code' => 'N/A',
            // ],
            [
                'product_id' => $this->getProductId("G1238"),
                'process_code' => '2',
            ],
            [
                'product_id' => $this->getProductId("G1242"),
                'process_code' => '2',
            ],
            [
                'product_id' => $this->getProductId("G1242"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1242"),
                'process_code' => '4',
            ],
            [
                'product_id' => $this->getProductId("G1243"),
                'process_code' => '0',
            ],
            [
                'product_id' => $this->getProductId("G1243"),
                'process_code' => '1',
            ],
            [
                'product_id' => $this->getProductId("G1243"),
                'process_code' => '2',
            ],
            [
                'product_id' => $this->getProductId("G1243"),
                'process_code' => '3',
            ],
            [
                'product_id' => $this->getProductId("G1245"),
                'process_code' => '4',
            ],
            [
                'product_id' => $this->getProductId("G1245"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1245"),
                'process_code' => '6',
            ],
            [
                'product_id' => $this->getProductId("G1245"),
                'process_code' => '10',
            ],
            [
                'product_id' => $this->getProductId("G1246"),
                'process_code' => '6',
            ],
            [
                'product_id' => $this->getProductId("G1246"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1248"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1249"),
                'process_code' => '8',
            ],

            # 70
            [
                'product_id' => $this->getProductId("G1250"),
                'process_code' => '3',
            ],
            [
                'product_id' => $this->getProductId("G1250"),
                'process_code' => '10',
            ],
            [
                'product_id' => $this->getProductId("G1251"),
                'process_code' => '0',
            ],
            [
                'product_id' => $this->getProductId("G1252"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1253"),
                'process_code' => '8',
            ],
            // [
            //     'product_id' => $this->getProductId("G1258"),
            //     'process_code' => 'N/A',
            // ],
            [
                'product_id' => $this->getProductId("G1265"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1266"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1267"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1272"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1276"),
                'process_code' => '10',
            ],

            #80
            [
                'product_id' => $this->getProductId("G1286"),
                'process_code' => '1',
            ],
            [
                'product_id' => $this->getProductId("G1287"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1295"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1296"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1319"),
                'process_code' => '3',
            ],
            [
                'product_id' => $this->getProductId("G1325"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1326"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1327"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1334"),
                'process_code' => '4',
            ],
            [
                'product_id' => $this->getProductId("G1334"),
                'process_code' => '5',
            ],
            [
                'product_id' => $this->getProductId("G1334"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1334"),
                'process_code' => '10',
            ],
            [
                'product_id' => $this->getProductId("G1335"),
                'process_code' => '8',
            ],

            # 90
            [
                'product_id' => $this->getProductId("G1336"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G5004"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G5006"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G5012"),
                'process_code' => '8',
            ],
            [
                'product_id' => $this->getProductId("G1034"),
                'process_code' => '0',
            ],
            # 95


        ];

        DB::table('product_processes')->insert($product_processes);
    }
}
