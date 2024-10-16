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
    
    public function run()
    {
        $data = [
            ['code' => 'G0110', 'description' => 'Pig, Carcass', 'unit_of_measure' => 'KG', 'product_type' => 2, 'process_type' => 0],
            ['code' => 'G1102', 'description' => 'Pork Middle', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => 2],
            ['code' => 'G1108', 'description' => 'Sow Leg', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => 3],
            ['code' => 'G1109', 'description' => 'Sow Shoulder', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => 3],
            ['code' => 'G1110', 'description' => 'Sow Middle', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => 3],
            ['code' => 'G12241', 'description' => 'Sliced Pork Belly B\'ss R\'ss', 'unit_of_measure' => 'KG', 'product_type' => null, 'process_type' => null],
            ['code' => 'G1281', 'description' => 'Rolled B\'ss R\'ss Loin', 'unit_of_measure' => 'KG', 'product_type' => null, 'process_type' => null],
            ['code' => 'G1116', 'description' => 'Sow Bacon R\'ss', 'unit_of_measure' => 'KG', 'product_type' => null, 'process_type' => null],
            ['code' => 'G1304', 'description' => 'Pork Strips (from Lean Pork)', 'unit_of_measure' => 'KG', 'product_type' => 2, 'process_type' => null],
            ['code' => 'G1126A', 'description' => 'Pk Leg//Shoulder Sausages', 'unit_of_measure' => 'KG', 'product_type' => null, 'process_type' => null],
            ['code' => 'G1269', 'description' => 'Cooked Rinds', 'unit_of_measure' => 'KG', 'product_type' => null, 'process_type' => null],
            ['code' => 'G1314', 'description' => 'Cooked Soft Fat', 'unit_of_measure' => 'KG', 'product_type' => null, 'process_type' => null],
            ['code' => 'G1315', 'description' => 'Cooked Pork Celestine', 'unit_of_measure' => 'KG', 'product_type' => null, 'process_type' => null],
            ['code' => 'G1270', 'description' => 'Cooked Cooking Fat', 'unit_of_measure' => 'KG', 'product_type' => null, 'process_type' => null],
            ['code' => 'G1321', 'description' => 'Blood Plasma', 'unit_of_measure' => 'KG', 'product_type' => null, 'process_type' => null],
            ['code' => 'G5513', 'description' => 'Marinated Commercial Topside', 'unit_of_measure' => 'KG', 'product_type' => null, 'process_type' => null],
            ['code' => 'G1515', 'description' => 'Commercial Topside [U]', 'unit_of_measure' => 'KG', 'product_type' => null, 'process_type' => null],
            ['code' => 'G1575', 'description' => 'Boneless Beef Chuck', 'unit_of_measure' => 'KG', 'product_type' => null, 'process_type' => null],
            ['code' => 'G1464', 'description' => 'Lean Beef', 'unit_of_measure' => 'KG', 'product_type' => null, 'process_type' => null],
            ['code' => 'G1470', 'description' => 'Beef Blood Meat', 'unit_of_measure' => 'KG', 'product_type' => null, 'process_type' => null],
            ['code' => 'G1471', 'description' => 'Beef Fat', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G1569', 'description' => 'Lean Meat [Ox-head]', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G1570', 'description' => 'Jaw Meat [Ox-head]', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G1554', 'description' => 'Braising Steaks', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G1539', 'description' => 'Continental Beef[M]', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G1516', 'description' => 'Commercial Striploin [U]', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G1338', 'description' => 'Pork Blade Meat (Imp) for Ssg', 'unit_of_measure' => 'KG', 'product_type' => 2, 'process_type' => null],
            ['code' => 'G1537', 'description' => 'Commercial Rump Steak [U]', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G1560', 'description' => 'Commercial SilverSide, BBc/Sal', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G5507', 'description' => 'Marinated Silverside', 'unit_of_measure' => 'KG', 'product_type' => 2, 'process_type' => null],
            ['code' => 'G1239', 'description' => 'Pork Liver', 'unit_of_measure' => 'KG', 'product_type' => 2, 'process_type' => null],
            ['code' => 'G1440', 'description' => 'Topside (U) [Sale]', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G5531', 'description' => 'Marinated Beef Neck B\'ss', 'unit_of_measure' => 'KG', 'product_type' => 2, 'process_type' => null],
            ['code' => 'G1447', 'description' => 'Silverside (Beef Bacon/Sale)', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G1449', 'description' => 'Rump Steak High Grade [U]', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G1938', 'description' => 'Reformed Lamb Leg Meat', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G1553', 'description' => 'Beef Hump', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G1563', 'description' => 'Commercial Thck-Flank/Top Rump', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G1468', 'description' => 'Beef Cubes [M]', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G1980', 'description' => 'Reformed Lamb Shoulder Meat', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G1291', 'description' => 'Lean Pork - [Sausage]', 'unit_of_measure' => 'KG', 'product_type' => 2, 'process_type' => null],
            ['code' => 'G1493', 'description' => 'Thick Flank/Top Rump/StewStk', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G2150', 'description' => 'Soya Gel', 'unit_of_measure' => 'KG', 'product_type' => 2, 'process_type' => null],
            ['code' => 'G2159', 'description' => 'Semi Lean Veg Emul Ln/Beef Mix', 'unit_of_measure' => 'KG', 'product_type' => 2, 'process_type' => null],
            ['code' => 'G5512', 'description' => 'Marinated Topside - 1440', 'unit_of_measure' => 'KG', 'product_type' => 2, 'process_type' => null],
            ['code' => 'G5527', 'description' => 'Marinated Comm. Thick Frank', 'unit_of_measure' => 'KG', 'product_type' => 2, 'process_type' => null],
            ['code' => 'G1443', 'description' => 'Topside, Pressed Beef/Marinate', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G1481', 'description' => 'Ox-Kidney', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null],
            ['code' => 'G2323', 'description' => 'Filled Smokies, (STD)', 'unit_of_measure' => 'KG', 'product_type' => 1, 'process_type' => null]

        ];

        DB::table('products')->insert($data);
    }
}
