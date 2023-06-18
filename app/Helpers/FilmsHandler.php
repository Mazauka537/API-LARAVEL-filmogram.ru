<?php

namespace App\Helpers;

use App\Models\Collection;
use App\Models\Film;

class FilmsHandler
{
    public static function setIsInFavoriteFlag($films = [], $user = null)
    {
        foreach ($films as $film) {
            $film->isInFavorite = false;
        }

        if ($user) {
            $filmsIds = [];

            foreach ($films as $film) {
                $filmsIds[] = $film->film_id;
            }

            $favoriteCollection = Collection::where('user_id', $user->id)->where('constant', true)->firstOrFail();

            $favoriteFilms = Film::where('collection_id', $favoriteCollection->id)
                ->whereIn('film_id', $filmsIds)
                ->get();

            foreach ($films as $film) {
                foreach ($favoriteFilms as $favoriteFilm) {
                    if ($film->film_id === $favoriteFilm->film_id) {
                        $film->isInFavorite = true;
                        break;
                    }
                }
            }
        }

        return $films;
    }
}
