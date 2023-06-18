<?php

namespace App\Http\Controllers;

use App\Helpers\CollectionsHandler;
use App\Helpers\FilmsHandler;
use App\Helpers\KinopoiskAPI;
use App\Helpers\LoadableItems;
use App\Helpers\UserAuthorizer;
use App\Helpers\UsersHandler;
use App\Models\Collection;
use App\Models\Film;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SearchController extends Controller
{
    private function _searchUsers(Request $request, $page, $itemsPerPage) {

        $usersCount = User::where('name', 'LIKE', '%' . $request->keyword . '%')->count();

        [$limit, $skip, $totalPages] = LoadableItems::getData($usersCount, $page, $itemsPerPage);

        if ($totalPages === 0) {
            return ['totalPages' => $totalPages, 'items' => []];
        }

        $users = User::where('name', 'LIKE', '%' . $request->keyword . '%')
            ->withCount('subscribers as subscribers')
            ->withCount('subscriptions as subscriptions')
            ->latest()
            ->skip($skip)
            ->take($limit)
            ->get();

        $users = UsersHandler::setIsSubscribedFlag($users, UserAuthorizer::authorize($request));

        return ['totalPages' => $totalPages, 'items' => $users];
    }

    private function _searchCollection(Request $request, $page, $itemsPerPage) {
        $collectionsCount = Collection::where('title', 'LIKE', '%' . $request->keyword . '%')
            ->where('constant', false)
            ->where('public', true)
            ->count();

        [$limit, $skip, $totalPages] = LoadableItems::getData($collectionsCount, $page, $itemsPerPage);

        if ($totalPages === 0) {
            return ['totalPages' => $totalPages, 'items' => []];
        }

        $collections = Collection::where('title', 'LIKE', '%' . $request->keyword . '%')
            ->where('constant', false)
            ->where('public', true)
            ->with('user')
            ->withCount('films')
            ->skip($skip)
            ->take($limit)
            ->get();

        $collections = CollectionsHandler::setIsInSavesFlag($collections, UserAuthorizer::authorize($request));

        foreach ($collections as $collection) {
            if (!$collection->image) {
                $collection->posters = CollectionsHandler::getPosters($collection->id);
            }
        }

        return ['totalPages' => $totalPages, 'items' => $collections];
    }

    private function _searchFilms(Request $request, $page) {
        $response = KinopoiskAPI::searchFilms($request, $page);
        $filmsResponseData = $response['data'];

        if (!$filmsResponseData->items) {
            return $filmsResponseData;
        }

        foreach ($filmsResponseData->items as $key => $value) {
            $filmsResponseData->items[$key] = (object)[
                'film_id' => $value->kinopoiskId,
                'filmKp' => $value
            ];
        }

        $filmsResponseData->items = FilmsHandler::setIsInFavoriteFlag($filmsResponseData->items, UserAuthorizer::authorize($request));

        return $filmsResponseData;
    }



    public function searchAll(Request $request) {
        $request->validate([
            'keyword' => 'nullable|string',
        ]);

        $responseData = [];

        $responseData['films'] = $this->_searchFilms($request, 1)->items;
        $responseData['collections'] = $this->_searchCollection($request, 1, 11)['items'];
        $responseData['users'] = $this->_searchUsers($request, 1, 12)['items'];

        return response($responseData, 200);
    }

    public function searchFilms(Request $request) {
        $request->validate([
            'page' => 'required|integer|min:1',
            'collection_id' => 'integer|nullable',
            'keyword' => 'nullable|string',
            'order' => ['string', 'nullable', Rule::in(['NUM_VOTE', 'RATING', 'YEAR'])],
            'type' => ['string', 'nullable', Rule::in(['FILM', 'TV_SHOW', 'TV_SERIES', 'MINI_SERIES', 'ALL'])],
            'genre' => 'nullable|integer',
            'year_from' => 'integer|nullable|between:1000,3000',
            'year_to' => 'integer|nullable|between:1000,3000',
            'rating_from' => 'integer|nullable|between:0,10',
            'rating_to' => 'integer|nullable|between:0,10',
        ]);

        if ($request->year_from && $request->year_to && $request->year_from > $request->year_to) {
            return response([
                "message" => "The given data was invalid.",
                "errors" => [
                    "year_from" => ["The year from must be less than or equal to year to."],
                ]
            ], 422);
        }
        if ($request->rating_from && $request->rating_to && $request->rating_from > $request->rating_to) {
            return response([
                "message" => "The given data was invalid.",
                "errors" => [
                    "rating_from" => ["The rating from must be less than or equal to rating to."]
                ]
            ], 422);
        }

        $filmsResponseData = $this->_searchFilms($request, $request->page);
        if (!$filmsResponseData->items) {
            return response(json_encode($filmsResponseData), 200);
        }

        if ($request->collection_id) {

            $filmsIds = [];

            foreach ($filmsResponseData->items as $searchedFilm) {
                $filmsIds[] = $searchedFilm->film_id;
            }

            $collectedFilms = Film::where('collection_id', $request->collection_id)->whereIn('film_id', $filmsIds)->get();

            foreach ($filmsResponseData->items as $searchedFilm) {
                $isInCollection = false;
                foreach ($collectedFilms as $collectedFilm) {
                    if ($searchedFilm->film_id === $collectedFilm->film_id) {
                        $isInCollection = true;
                    }
                }

                $searchedFilm->isInCollection = $isInCollection;
            }

        }


        return response(json_encode($filmsResponseData), 200);
    }

    public function searchCollections(Request $request)
    {
        $request->validate([
            'keyword' => 'nullable|string',
            'page' => 'required|integer|min:1',
            'limit' => 'nullable|integer'
        ]);

        $collectionsResponseData = $this->_searchCollection($request, $request->page, $request->limit ?? 40);

        return response($collectionsResponseData, 200);
    }

    public function searchUsers(Request $request)
    {
        $request->validate([
            'keyword' => 'nullable|string',
            'page' => 'required|integer|min:1'
        ]);

        $usersResponseData = $this->_searchUsers($request, $request->page, 40);

        return response($usersResponseData, 200);
    }
}
