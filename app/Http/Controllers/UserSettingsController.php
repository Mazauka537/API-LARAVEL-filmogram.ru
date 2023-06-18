<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserSettingsController extends Controller
{
    public function toggleSubscribeNotifications(Request $request)
    {
        Auth::user()->is_notification_subscribe_enabled = !Auth::user()->is_notification_subscribe_enabled;
        Auth::user()->save();

        return response('', Auth::user()->is_notification_subscribe_enabled ? 201 : 200);
    }

    public function toggleLikeNotifications(Request $request)
    {
        Auth::user()->is_notification_like_enabled = !Auth::user()->is_notification_like_enabled;
        Auth::user()->save();

        return response('', Auth::user()->is_notification_like_enabled ? 201 : 200);
    }
}
