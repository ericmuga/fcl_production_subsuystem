<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FamilySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['family_no' => '1210J01', 'item_no' => 'G0110', 'family_description' => 'Behead Pig', 'item_type' => 'Intake', 'process_code' => 0],
            ['family_no' => '1210J01', 'item_no' => 'G1030', 'family_description' => 'Behead Pig', 'item_type' => 'Output', 'process_code' => 0],
            ['family_no' => '1210J01', 'item_no' => 'G1228', 'family_description' => 'Behead Pig', 'item_type' => 'Output', 'process_code' => 0],
            ['family_no' => '1210J01', 'item_no' => 'G1229', 'family_description' => 'Behead Pig', 'item_type' => 'Output', 'process_code' => 0],
            ['family_no' => '1210J01', 'item_no' => 'G1234', 'family_description' => 'Behead Pig', 'item_type' => 'Output', 'process_code' => 0],
            ['family_no' => '1210J01', 'item_no' => 'G1235', 'family_description' => 'Behead Pig', 'item_type' => 'Output', 'process_code' => 0],
            ['family_no' => '1210J01', 'item_no' => 'G1236', 'family_description' => 'Behead Pig', 'item_type' => 'Output', 'process_code' => 0],
            ['family_no' => '1210J01', 'item_no' => 'G1238', 'family_description' => 'Behead Pig', 'item_type' => 'Output', 'process_code' => 0],
            ['family_no' => '1210J01', 'item_no' => 'G1243', 'family_description' => 'Behead Pig', 'item_type' => 'Output', 'process_code' => 0],
            ['family_no' => '1210J01', 'item_no' => 'G1244', 'family_description' => 'Behead Pig', 'item_type' => 'Output', 'process_code' => 0],
            ['family_no' => '1210J01', 'item_no' => 'G1251', 'family_description' => 'Behead Pig', 'item_type' => 'Output', 'process_code' => 0],
            ['family_no' => '1210J02', 'item_no' => 'G0111', 'family_description' => 'Behead Sow', 'item_type' => 'Intake', 'process_code' => 1],
            ['family_no' => '1210J02', 'item_no' => 'G1031', 'family_description' => 'Behead Sow', 'item_type' => 'Output', 'process_code' => 1],
            ['family_no' => '1210J02', 'item_no' => 'G1234', 'family_description' => 'Behead Sow', 'item_type' => 'Output', 'process_code' => 1],
            ['family_no' => '1210J02', 'item_no' => 'G1235', 'family_description' => 'Behead Sow', 'item_type' => 'Output', 'process_code' => 1],
            ['family_no' => '1210J02', 'item_no' => 'G1236', 'family_description' => 'Behead Sow', 'item_type' => 'Output', 'process_code' => 1],
            ['family_no' => '1210J02', 'item_no' => 'G1238', 'family_description' => 'Behead Sow', 'item_type' => 'Output', 'process_code' => 1],
            ['family_no' => '1210J02', 'item_no' => 'G1243', 'family_description' => 'Behead Sow', 'item_type' => 'Output', 'process_code' => 1],
            ['family_no' => '1210J02', 'item_no' => 'G1286', 'family_description' => 'Behead Sow', 'item_type' => 'Output', 'process_code' => 1],
            ['family_no' => '1210J04', 'item_no' => 'G0110', 'family_description' => 'Behead/Transfer Porker-->Blast', 'item_type' => 'Intake', 'process_code' => 41],
            ['family_no' => '1210J04', 'item_no' => 'G1033', 'family_description' => 'Behead/Transfer Porker-->Blast', 'item_type' => 'Output', 'process_code' => 41],
            ['family_no' => '1210J04', 'item_no' => 'G1228', 'family_description' => 'Behead/Transfer Porker-->Blast', 'item_type' => 'Output', 'process_code' => 41],
            ['family_no' => '1210J04', 'item_no' => 'G1229', 'family_description' => 'Behead/Transfer Porker-->Blast', 'item_type' => 'Output', 'process_code' => 41],
            ['family_no' => '1210J04', 'item_no' => 'G1234', 'family_description' => 'Behead/Transfer Porker-->Blast', 'item_type' => 'Output', 'process_code' => 41],
            ['family_no' => '1210J04', 'item_no' => 'G1235', 'family_description' => 'Behead/Transfer Porker-->Blast', 'item_type' => 'Output', 'process_code' => 41],
            ['family_no' => '1210J04', 'item_no' => 'G1236', 'family_description' => 'Behead/Transfer Porker-->Blast', 'item_type' => 'Output', 'process_code' => 41],
            ['family_no' => '1210J04', 'item_no' => 'G1238', 'family_description' => 'Behead/Transfer Porker-->Blast', 'item_type' => 'Output', 'process_code' => 41],
            ['family_no' => '1210J04', 'item_no' => 'G1243', 'family_description' => 'Behead/Transfer Porker-->Blast', 'item_type' => 'Output', 'process_code' => 41],
            ['family_no' => '1210J04', 'item_no' => 'G1244', 'family_description' => 'Behead/Transfer Porker-->Blast', 'item_type' => 'Output', 'process_code' => 41],
            ['family_no' => '1210J04', 'item_no' => 'G1251', 'family_description' => 'Behead/Transfer Porker-->Blast', 'item_type' => 'Output', 'process_code' => 41],
        ];

        DB::table('family')->insert($data);
    }
}
