<?php

namespace App\Http\Controllers;

use App\Helpers\FilmsHandler;
use App\Helpers\KinopoiskAPI;
use App\Helpers\LoadableItems;
use App\Helpers\UserAuthorizer;
use App\Models\Collection;
use App\Models\Film;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FilmController extends Controller
{
    public function toggleFavorite(Request $request)
    {
        $request->validate([
            'film_id' => 'required|integer',
        ]);

        $favoriteCollection = Collection::where('user_id', Auth::id())->where('constant', true)->first();

        $film = Film::where('film_id', $request->film_id)->where('collection_id', $favoriteCollection->id)->first();

        if (!$film) {
            $order = Film::where('collection_id', $favoriteCollection->id)->max('order');
            if (!$order) {
                $order = 1000;
            }
            Film::create([
                'collection_id' => $favoriteCollection->id,
                'film_id' => $request->film_id,
                'order' => $order + 1000
            ]);

            return response('', 201);
        } else {
            $film->delete();

            return response('', 200);
        }
    }

    public function getFilms(Request $request)
    {
        $request->validate([
            'collection_id' => 'required|integer',
            'page' => 'required|integer|min:1'
        ]);

        $filmsCount = Film::where('collection_id', $request->collection_id)->count();

        [$limit, $skip, $totalPages] = LoadableItems::getData($filmsCount, $request->page);

        if ($totalPages === 0) {
            return response(['totalPages' => $totalPages, 'totalCount' => $filmsCount, 'films' => []], 200);
        }

        $collectedFilms = Film::where('collection_id', $request->collection_id)
            ->orderByDesc('order')
            ->skip($skip)
            ->limit($limit)
            ->get();

        foreach ($collectedFilms as $collectedFilm) {
            $response = KinopoiskAPI::getFilm($collectedFilm->film_id);
            $collectedFilm['filmKp'] = $response['data'];
        }

        $collectedFilms = FilmsHandler::setIsInFavoriteFlag($collectedFilms, UserAuthorizer::authorize($request));

        return response(['totalPages' => $totalPages, 'totalCount' => $filmsCount, 'items' => $collectedFilms], 200);
    }

    public function setOrder(Request $request)
    {
        $request->validate([
            'film_id' => 'required|integer',
            'order' => 'required|numeric|min:0'
        ]);

        $film = Film::with('collection')->findOrFail($request->film_id);
        if ($film->collection->user_id !== Auth::id()) {
            return response('forbidden', 403);
        }

        $film->order = $request->order;
        $film->save();

        return response('', 200);
    }
}
