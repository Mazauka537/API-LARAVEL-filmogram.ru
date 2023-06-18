<?php

namespace App\Http\Controllers;

use App\Helpers\CollectionsHandler;
use App\Helpers\KinopoiskAPI;
use App\Helpers\LoadableItems;
use App\Helpers\NotificationsSender;
use App\Helpers\UserAuthorizer;
use App\Models\Collection;
use App\Models\Film;
use App\Models\Save;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SaveController extends Controller
{
    public function getSaves(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'page' => 'required|integer|min:1'
        ]);

        $userOwner = User::findOrFail($request->user_id);
        $userRequestor = UserAuthorizer::authorize($request);

        $accessAllowed = (bool)$userOwner->is_saves_public;

        if ($userRequestor) {
            if ($userRequestor->id === $userOwner->id) {
                $accessAllowed = true;
            }
        }

        if (!$accessAllowed) {
            return response('access denied', 403);
        }

        $savesCount = $userOwner->saves()->count();

        [$limit, $skip, $totalPages] = LoadableItems::getData($savesCount, $request->page);

        if ($totalPages === 0) {
            return response(['totalPages' => $totalPages, 'items' => []], 200);
        }

        $saves = $userOwner->saves()
            ->with('user')
            ->withCount('films')
            ->latest()
            ->skip($skip)
            ->take($limit)
            ->get();

        $saves = CollectionsHandler::setIsInSavesFlag($saves, $userRequestor);

        foreach ($saves as $collection) {
            if (!$collection->image) {
                $collection->posters = CollectionsHandler::getPosters($collection->id);
            }
        }

        return response(['totalPages' => $totalPages, 'items' => $saves], 200);
    }

    public function toggleCollection(Request $request)
    {
        $request->validate([
            'collection_id' => 'required|integer',
        ]);

        $collection = Collection::findOrFail($request->collection_id);
        if ($collection->user_id === Auth::id()) {
            return response('', 403);
        }

        $save = Save::where('collection_id', $request->collection_id)->where('user_id', Auth::id())->first();

        if (!$save) {
            Save::create([
                'collection_id' => $request->collection_id,
                'user_id' => Auth::id(),
            ]);

            $usersSettings = User::select('is_notification_like_enabled')->findOrFail($collection->user_id);
            if ($usersSettings->is_notification_like_enabled) {

                NotificationsSender::send($collection->user_id,
                    'Ваша коллекция кому-то понравилась!',
                    'Пользователь ' . Auth::user()->name . ' сохранил себе вашу коллекцию "' . $collection->title . '"',
                    config('client_app.url') . '/collection/' . $collection->id
                );
            }

            return response('', 201);
        } else {
            $save->delete();

            return response('', 200);
        }
    }
}
