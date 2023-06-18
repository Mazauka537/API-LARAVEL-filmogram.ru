<?php

namespace App\Http\Controllers;

use App\Helpers\LoadableItems;
use App\Helpers\NotificationsSender;
use App\Helpers\UserAuthorizer;
use App\Helpers\UsersHandler;
use App\Models\Follower;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowerController extends Controller
{
    public function toggleSubscription(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer'
        ]);

        if (Auth::id() === $request->user_id) {
            return response('', 400);
        }

        $user = User::findOrFail($request->user_id);

        $follower = Follower::where('user_id', Auth::id())->where('follow_id', $request->user_id)->first();

        if (!$follower) {
            Follower::create([
                'user_id' => Auth::id(),
                'follow_id' => $request->user_id,
            ]);

            $usersSettings = User::select('is_notification_subscribe_enabled')->findOrFail($request->user_id);
            if ($usersSettings->is_notification_subscribe_enabled) {

                NotificationsSender::send($request->user_id,
                    'Новый подписчик!',
                    'Пользователь ' . Auth::user()->name . ' подписался на вас',
                    config('client_app.url') . '/user/' . Auth::id()
                );
            }

            return response('', 201);
        } else {
            $follower->delete();

            return response('', 200);
        }
    }

    public function getSubscribers(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'page' => 'required|integer'
        ]);

        $subscribersCount = Follower::where('follow_id', $request->user_id)->count();

        [$limit, $skip, $totalPages] = LoadableItems::getData($subscribersCount, $request->page);

        if ($totalPages === 0) {
            return response(['totalPages' => $totalPages, 'items' => []], 200);
        }

        $user = User::findOrFail($request->user_id);

        $subscribers = $user->subscribers()
            ->withCount('subscribers as subscribers')
            ->withCount('subscriptions as subscriptions')
            ->latest()
            ->skip($skip)
            ->take($limit)
            ->get();

        $subscribers = UsersHandler::setIsSubscribedFlag($subscribers, UserAuthorizer::authorize($request));


        return response(['totalPages' => $totalPages, 'items' => $subscribers], 200);
    }

    public function getSubscriptions(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer',
            'page' => 'required|integer'
        ]);

        $subscriptionsCount = Follower::where('user_id', $request->user_id)->count();
        $limit = 10;
        $skip = ($request->page - 1) * $limit;

        $totalPages = ceil($subscriptionsCount / $limit);
        if ($totalPages === 0) {
            return response(['totalPages' => $totalPages, 'items' => []], 200);
        }

        $user = User::findOrFail($request->user_id);

        $subscriptions = $user->subscriptions()
            ->withCount('subscribers as subscribers')
            ->withCount('subscriptions as subscriptions')
            ->latest()
            ->skip($skip)
            ->take($limit)
            ->get();

        $subscriptions = UsersHandler::setIsSubscribedFlag($subscriptions, UserAuthorizer::authorize($request));

        return response(['totalPages' => $totalPages, 'items' => $subscriptions], 200);
    }
}
