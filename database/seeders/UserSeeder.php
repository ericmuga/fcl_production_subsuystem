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
                'email' => Str::random(10).'@gmail.com',
                'password' => Hash::make('1234'),
                'section' => 'slaughter',
            ],
            [
                'username' => 'User1',
                'email' => Str::random(10).'@gmail.com',
                'password' => Hash::make('1234'),
                'section' => 'butchery',
            ],
            [
                'username' => 'User2',
                'email' => Str::random(10).'@gmail.com',
                'password' => Hash::make('1234'),
                'section' => 'admin',
            ],
            [
                'username' => 'User3',
                'email' => Str::random(10).'@gmail.com',
                'password' => Hash::make('1234'),
                'section' => 'admin',
            ],
            [
                'username' => 'User4',
                'email' => Str::random(10).'@gmail.com',
                'password' => Hash::make('1234'),
                'section' => 'admin',
            ]
        ];

        DB::table('users')->insert($users);
    }
}
