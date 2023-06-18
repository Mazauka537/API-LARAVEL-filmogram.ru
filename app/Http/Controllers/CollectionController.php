<?php

namespace App\Http\Controllers;

use App\Helpers\CollectionsHandler;
use App\Helpers\ImageSaver;
use App\Helpers\KinopoiskAPI;
use App\Helpers\LoadableItems;
use App\Helpers\UserAuthorizer;
use App\Models\Collection;
use App\Models\DefaultCollection;
use App\Models\Film;
use App\Rules\Base64;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CollectionController extends Controller
{
    private $imagesPath = 'public/images/collections/';

    public function createCollection(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:90',
            'description' => 'nullable|string|max:255',
            'image' => ['nullable', new Base64(['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])],
        ]);

        $collection = Collection::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'description' => $request->description ?? null
        ]);

        $fileName = ImageSaver::saveImage($request->image, $this->imagesPath, $collection->id, $collection->image, $request->isImageDeleted);

        $collection->image = $fileName;
        $collection->save();

        $collection->load('user')->loadCount('films');

        if (!$collection->image) {
            $collection->posters = [];
        }

        return response($collection, 201);
    }

    public function editCollection(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'title' => 'required|string|max:90',
            'description' => 'nullable|string|max:255',
            'image' => ['nullable', new Base64(['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])],
            'isImageDeleted' => 'required|boolean'
        ]);


        $collection = Collection::with('user')
            ->withCount('films')
            ->where('user_id', Auth::id())
            ->findOrFail($request->id);

        if ($collection->constant) {
            return response('', 403);
        }

        $fileName = ImageSaver::saveImage($request->image, $this->imagesPath, $collection->id, $collection->image, $request->isImageDeleted);

        $collection->image = $fileName;
        $collection->title = $request->title;
        $collection->description = $request->description;
        $collection->save();

        if (!$collection->image) {
            $collection->posters = CollectionsHandler::getPosters($collection->id);
        }

        return response($collection, 200);
    }

    public function deleteCollection(Request $request)
    {
        $request->validate([
            'collection_id' => 'required|integer'
        ]);

        $collection = Collection::findOrFail($request->collection_id);

        if ($collection->user_id !== Auth::id()) {
            return response('forbidden', 403);
        }

        $collection->delete();

        return response('', 200);
    }

    public function toggleCollectionPublic(Request $request)
    {
        $request->validate([
            'collection_id' => 'required|integer',
        ]);

        $collection = Collection::where('user_id', Auth::id())->findOrFail($request->collection_id);

        $collection->public = !$collection->public;
        $collection->save();

        if (!$collection->public) {
            return response('', 201);
        } else {
            return response('', 200);
        }
    }

    public function getCollection(Request $request)
    {
        $request->validate([
            'id' => 'required|integer'
        ]);

        $collection = Collection::with('user')
            ->withCount('saves')
            ->withCount('films')
            ->findOrFail($request->id);

        $collection = CollectionsHandler::setIsInSavesFlag([$collection], UserAuthorizer::authorize($request))[0];

        return response($collection, 200);
    }

    public function getFamousCollections(Request $request)
    {
        $collections = Collection::where('public', true)
            ->withCount('saves')
            ->orderByDesc('saves_count')
            ->limit(11)
            ->get();

        $collections = CollectionsHandler::setIsInSavesFlag($collections, UserAuthorizer::authorize($request));

        foreach ($collections as $collection) {
            if (!$collection->image) {
                $collection->posters = CollectionsHandler::getPosters($collection->id);
            }
        }

        return response($collections, 200);
    }

    public function getCollections(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'page' => 'required|integer|min:1',
            'limit' => 'nullable|integer'
        ]);

        $user = UserAuthorizer::authorize($request);

        $onlyPublic = !($user && $request->user_id == $user->id);

        $query = Collection::where('user_id', $request->user_id);
        if ($onlyPublic) {
            $query = $query->where('public', true);
        }
        $collectionsCount = $query->count();

        [$limit, $skip, $totalPages] = LoadableItems::getData($collectionsCount, $request->page, $request->limit ?? 40);

        if ($totalPages === 0) {
            return response(['totalPages' => $totalPages, 'items' => []], 200);
        }

        $query = Collection::with('user')
            ->withCount('films')
            ->where('user_id', $request->user_id);

        if ($onlyPublic) {
            $query = $query->where('public', true);
        }

        $collections = $query
            ->orderByDesc('constant')
            ->orderByDesc('updated_at')
            ->skip($skip)
            ->limit($limit)
            ->get();

        $collections = CollectionsHandler::setIsInSavesFlag($collections, UserAuthorizer::authorize($request));

        foreach ($collections as $collection) {
            if (!$collection->image) {
                $collection->posters = CollectionsHandler::getPosters($collection->id);
            }
        }

        return response(['totalPages' => $totalPages, 'items' => $collections], 200);
    }

    public function getAllCollections(Request $request)
    {
        $request->validate([
            'film_id' => 'required|integer'
        ]);

        $collections = Auth::user()->collections()->with(['films' => function ($query) use ($request) {
            $query->where('films.film_id', $request->film_id);
        }])->get();

        foreach ($collections as $collection) {
            $collection->isFilmAdded = count($collection->films) > 0;
        }

        return response($collections, 200);
    }

    public function getNavCollections(Request $request)
    {
        $collections = Collection::with('user')
            ->withCount('films')
            ->where('user_id', Auth::id())
            ->orderByDesc('constant')
            ->orderByDesc('updated_at')
            ->get();

        foreach ($collections as $collection) {
            if (!$collection->image) {
                $collection->posters = CollectionsHandler::getPosters($collection->id);
            }
        }

        return response($collections, 200);
    }

    public function toggleFilm(Request $request)
    {
        $request->validate([
            'collection_id' => 'required|integer',
            'film_id' => 'required|integer',
        ]);

        $collection = Collection::where('id', $request->collection_id)->where('user_id', Auth::id())->first();

        if (!$collection) {
            return response('forbidden', 403);
        }

        $film = Film::where('collection_id', $request->collection_id)->where('film_id', $request->film_id)->first();

        if (!$film) {
            $order = Film::where('collection_id', $request->collection_id)->max('order');
            if (!$order) {
                $order = 1000;
            }
            Film::create([
                'collection_id' => $request->collection_id,
                'film_id' => $request->film_id,
                'order' => $order + 1000
            ]);

            return response('', 201);
        } else {
            $film->delete();

            return response('', 200);
        }
    }
}
