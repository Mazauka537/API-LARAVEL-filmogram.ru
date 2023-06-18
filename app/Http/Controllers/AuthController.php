<?php

namespace App\Http\Controllers;

use App\Helpers\UserAuthorizer;
use App\Helpers\UsersHandler;
use App\Mail\RestorePasswordCode;
use App\Models\Collection;
use App\Models\RestoreCode;
use App\Models\User;
use Google_Client;
use Google_Service_Oauth2;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function setNewPassword(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
            'password' => 'required|string|between:6,16'
        ]);

        $restoreCode = RestoreCode::where('code', $request->code)->firstOrFail();

        $validRestoreCode = RestoreCode::where('user_id', $restoreCode->user_id)
            ->where('created_at', '>=', Carbon::now()->subMinutes(10)->toDateTimeString())
            ->orderByDesc('created_at')
            ->firstOrFail();

        if ($restoreCode->code !== $validRestoreCode->code) {
            return response('', 404);
        }

        User::where('id', $restoreCode->user_id)->update(['password' => Hash::make($request->password)]);

        return response('', 200);
    }

    public function restorePassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = User::where('email', $request->email)->firstOrFail();

        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = substr(str_shuffle($permitted_chars), 0, 12);

        RestoreCode::create([
            'code' => $code,
            'user_id' => $user->id
        ]);

        Mail::to($user)->send(new RestorePasswordCode($code));

        return response('', 200);
    }

    public function googleAuth(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        $clientID = env('GOOGLE_AUTH_CLIENT_ID');
        $clientSecret = env('GOOGLE_AUTH_CLIENT_SECRET');
        $redirectUri = env('GOOGLE_AUTH_REDIRECT_URI');

        $client = new Google_Client();
        $client->setClientId($clientID);
        $client->setClientSecret($clientSecret);
        $client->setRedirectUri($redirectUri);

        $token = $client->fetchAccessTokenWithAuthCode($request->code);
        $client->setAccessToken($token['access_token']);

        $google_oauth = new Google_Service_Oauth2($client);
        $google_account_info = $google_oauth->userinfo->get();

        $user = User::where('email', $google_account_info->email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $google_account_info->name,
                'email' => $google_account_info->email,
                'auth_service' => 'google',
            ]);

            Collection::create([
                'title' => 'Любимые фильмы',
                'user_id' => $user->id,
                'constant' => true,
                'image' => 'heart.png',
                'public' => false
            ]);
        }

        $user = User::withCount('subscribers as subscribers')
            ->withCount('subscriptions as subscriptions')
            ->where('email', $google_account_info->email)
            ->firstOrFail();

        if ($user->auth_service !== 'google') {
            return response('', 403);
        }

        $user = UsersHandler::setIsSubscribedFlag([$user], UserAuthorizer::authorize($request))[0];

        $token = $user->createToken('greatest_name')->plainTextToken;

        return response([
            'token' => $token,
            'user' => $user,
        ], 200);
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|between:6,16'
        ]);

        if ($validator->fails()) {
            if ($validator->errors()->first('email') === 'The email has already been taken.') {
                return response('', 406);
            }
        }

        $validator->validate();

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Collection::create([
            'title' => 'Любимые фильмы',
            'user_id' => $user->id,
            'constant' => true,
            'image' => 'heart.png',
            'public' => false
        ]);

        $token = $user->createToken('greatest_name')->plainTextToken;

        $user = User::withCount('subscribers as subscribers')
            ->withCount('subscriptions as subscriptions')
            ->where('email', $request->email)
            ->firstOrFail();

        $user = UsersHandler::setIsSubscribedFlag([$user], UserAuthorizer::authorize($request))[0];

        return response([
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::withCount('subscribers as subscribers')
            ->withCount('subscriptions as subscriptions')
            ->where('email', $request->email)
            ->firstOrFail();

        if ($user->auth_service !== null) {
            return response('', 403);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response('', 400);
        }

        $token = $user->createToken('greatest_name')->plainTextToken;

        $user = UsersHandler::setIsSubscribedFlag([$user], UserAuthorizer::authorize($request))[0];

        return response([
            'token' => $token,
            'user' => $user,
        ], 200);
    }

    public function auth(Request $request)
    {
        $user = User::withCount('subscribers as subscribers')
            ->withCount('subscriptions as subscriptions')
            ->find(Auth::id());

        $user = UsersHandler::setIsSubscribedFlag([$user], UserAuthorizer::authorize($request))[0];

        return response(['user' => $user], 200);
    }
}
