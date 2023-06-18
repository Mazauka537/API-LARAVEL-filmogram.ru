<?php

namespace App\Http\Controllers;

use App\Models\NotificationToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function addNotificationToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        NotificationToken::create([
            'token' => $request->token,
            'user_id' => Auth::id()
        ]);

        return response('', 200);
    }
}
