<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Film;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FilmSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement("SET foreign_key_checks=0");
        DB::table('films')->truncate();
        DB::statement("SET foreign_key_checks=1");

        Film::create([
            'collection_id' => 1,
            'film_id' => 14533,
            'order' => 1000
        ]);

        Film::create([
            'collection_id' => 1,
            'film_id' => 24333,
            'order' => 2000
        ]);
    }
}
