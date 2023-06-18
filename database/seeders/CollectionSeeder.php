<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CollectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("SET foreign_key_checks=0");
        DB::table('collections')->truncate();
        DB::statement("SET foreign_key_checks=1");

        Collection::create([
            'title' => 'test',
            'description' => 'test description',
            'user_id' => 1
        ]);

        Collection::create([
            'title' => 'test2',
            'description' => 'test2 description2',
            'user_id' => 2
        ]);
    }
}
