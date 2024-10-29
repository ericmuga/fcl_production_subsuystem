<?php

namespace Database\Seeders;

use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users =
        [
            // slaughter user
            [
                'username' => 'EKaranja',
                'email' => 'ekaranja@farmerschoice.co.ke',
                'password' => Hash::make('1234'),
                'section' => 'slaughter',
            ]
        ];

        DB::table('users')->insert($users);
    }
}
