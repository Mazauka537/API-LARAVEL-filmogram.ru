<?php

namespace App\Helpers;

use App\Models\Follower;
use Illuminate\Support\Facades\Log;

class UsersHandler {

    public static function setIsSubscribedFlag($users, $authorizedUser = null) {
        foreach ($users as $user) {
            $user->isSubscribed = false;
        }

        if ($authorizedUser) {
            $usersIds = [];

            foreach ($users as $user) {
                $usersIds[] = $user->id;
            }

            $followings = Follower::where('user_id', $authorizedUser->id)
                ->whereIn('follow_id', $usersIds)
                ->get();

            foreach ($users as $user) {
                foreach ($followings as $following) {
                    if ($user->id === $following->follow_id) {
                        $user->isSubscribed = true;
                    }
                }
            }
        }

        return $users;
    }
}
