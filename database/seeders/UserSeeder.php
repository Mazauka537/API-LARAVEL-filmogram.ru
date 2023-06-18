<?php

namespace Database\Seeders;

use App\Models\User;
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
        DB::statement("SET foreign_key_checks=0");
        DB::table('users')->truncate();
        DB::statement("SET foreign_key_checks=1");

        User::create([
            'name' => 'user',
            'email' => 'user@mail.ru',
            'password' => Hash::make('123456'),
        ]);

        User::create([
            'name' => 'user2',
            'email' => 'user2@mail.ru',
            'password' => Hash::make('123456'),
        ]);
    }
}
