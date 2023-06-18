<?php

namespace App\Helpers;

use App\Models\Film;
use App\Models\Save;

class CollectionsHandler {

    public static function getPosters($collectionId): array
    {
        $films = Film::where('collection_id', $collectionId)->orderByDesc('order')->take(4)->get();

        $posters = [];

        foreach ($films as $film) {
            $response = KinopoiskAPI::getFilm($film->film_id);
            $posters[] = $response['data']->posterUrlPreview;
        }

        return $posters;
    }

    public static function setIsInSavesFlag($collections = [], $user = null) {
        foreach ($collections as $collection) {
            $collection->isInSaves = false;
        }

        if ($user) {
            $collectionsIds = [];

            foreach ($collections as $collection) {
                $collectionsIds[] = $collection->id;
            }

            $saves = Save::where('user_id', $user->id)
                ->whereIn('collection_id', $collectionsIds)
                ->get();

            foreach ($collections as $collection) {
                foreach ($saves as $save) {
                    if ($collection->id === $save->collection_id) {
                        $collection->isInSaves = true;
                    }
                }
            }
        }

        return $collections;
    }
}
