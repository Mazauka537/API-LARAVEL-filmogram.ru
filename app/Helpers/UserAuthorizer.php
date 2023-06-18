<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class UserAuthorizer
{
    public static function authorize(Request $request)
    {
        $user = null;
        if (Auth::check()) {
            $user = Auth::user();
        } else {
            $token = PersonalAccessToken::findToken($request->bearerToken());
            if (!empty($token)) {
                $user = User::find($token->tokenable_id);
            }
        }

        return $user;
    }
}
