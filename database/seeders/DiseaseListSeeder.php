<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DiseaseListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['disease_code' => 'DELAY SET', 'description' => 'pig carcass delayed settlement'],
            ['disease_code' => 'DL[SELF]', 'description' => 'Died in Transit, Self Delivery'],
            ['disease_code' => 'DL-03', 'description' => 'Died in Lairage - 03'],
            ['disease_code' => 'DL-04', 'description' => 'Died in Lairage - 04'],
            ['disease_code' => 'DL-05', 'description' => 'Died in Lairage - 05'],
            ['disease_code' => 'DL-06', 'description' => 'Died in Lairage - 06'],
            ['disease_code' => 'DL-07', 'description' => 'Died in Lairage - 07'],
            ['disease_code' => 'DL-08', 'description' => 'Died in Lairage - 08'],
            ['disease_code' => 'DL-09', 'description' => 'Died in Lairage - 09'],
            ['disease_code' => 'DL-10', 'description' => 'Died in Lairage (3rdParty Sow)'],
            ['disease_code' => 'DL-RM03', 'description' => 'Died in Lairage - Rosemark 03'],
            ['disease_code' => 'DL-RM04', 'description' => 'Died in Lairage -04 (Rosemark)'],
            ['disease_code' => 'DL-RM05', 'description' => 'Died in Lairage -05 (Rosemark)'],
            ['disease_code' => 'DL-RM06', 'description' => 'Died in Lairage -06 (Rosemark)'],
            ['disease_code' => 'DL-RM07', 'description' => 'Died in Lairage -07 (Rosemark)'],
            ['disease_code' => 'DL-RM08', 'description' => 'Died in Lairage -08 (Rosemark)'],
            ['disease_code' => 'DL-RM09', 'description' => 'Died in Lairage -09 (Rosemark)'],
            ['disease_code' => 'DL-RM16', 'description' => 'Died in Lairage - Rosemark Sow'],
            ['disease_code' => 'DT-03', 'description' => 'Died in Transit(FCL) - CL-3'],
            ['disease_code' => 'DT-04', 'description' => 'Died in Transit(FCL) - CL-4'],
            ['disease_code' => 'DT-05', 'description' => 'Died in Transit(FCL) - CL-5'],
            ['disease_code' => 'DT-06', 'description' => 'Died in Transit(FCL) - CL-6'],
            ['disease_code' => 'DT-07', 'description' => 'Died in Transit(FCL) - CL-7'],
            ['disease_code' => 'DT-08', 'description' => 'Died in Transit(FCL) - CL-8'],
            ['disease_code' => 'DT-09', 'description' => 'Died in Transit (FCL) - Sow'],
            ['disease_code' => 'DT-RM03', 'description' => 'Died in Transit - Rosemark 03'],
            ['disease_code' => 'DT-RM16', 'description' => 'Died in Transit - Rosemark Sow'],
            ['disease_code' => 'FC10', 'description' => 'Condemned Pig'],
            ['disease_code' => 'FC20', 'description' => 'Held for Further Investigation'],
            ['disease_code' => 'FC30', 'description' => 'Black Pig'],
            ['disease_code' => 'FC60', 'description' => 'Pig is Boar'],
        ];

        DB::table('disease_list')->insert($data);
    }
}
