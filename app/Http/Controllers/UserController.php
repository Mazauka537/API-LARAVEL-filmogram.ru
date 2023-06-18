<?php

namespace App\Http\Controllers;

use App\Helpers\ImageSaver;
use App\Helpers\UserAuthorizer;
use App\Helpers\UsersHandler;
use App\Models\User;
use App\Rules\Base64;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function getFamousUsers()
    {
        $collections = User::withCount('subscribers')
            ->orderByDesc('subscribers_count')
            ->limit(11)
            ->get();

        return response($collections, 200);
    }

    public function getUser(Request $request)
    {
        $request->validate([
            'id' => 'required|integer'
        ]);

        $user = User::withCount('subscribers as subscribers')
            ->withCount('subscriptions as subscriptions')
            ->withCount(['collections as public_collections' => function ($query) {
                $query->where('public', true)->where('constant', false);
            }])
            ->findOrFail($request->id);


        $user = UsersHandler::setIsSubscribedFlag([$user], UserAuthorizer::authorize($request))[0];

        return response(['user' => $user], 200);
    }

    private $avatarsPath = 'public/images/avatars/';

    public function editUserData(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'image' => ['nullable', new Base64(['image/jpeg', 'image/jpg', 'image/png', 'image/gif'])],
            'isImageDeleted' => 'required|boolean'
        ]);

        $fileName = ImageSaver::saveImage($request->image, $this->avatarsPath, Auth::id(), Auth::user()->avatar, $request->isImageDeleted);

        Auth::user()->update(['name' => $request->name, 'avatar' => $fileName]);

        return response(Auth::user(), 200);
    }

    public function editEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users',
        ]);

        if ($validator->fails()) {
            if ($validator->errors()->first('email') === 'The email has already been taken.') {
                return response('', 406);
            }
        }

        $validator->validate();

        Auth::user()->update(['email' => $request->email]);

        return response(Auth::user(), 200);
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required|string',
            'new_password' => 'required|string|confirmed|between:6,16',
        ]);

        if (!Hash::check($request->old_password, Auth::user()->makeVisible(['password'])->password)) {
            return response('', 400);
        }

        Auth::user()->update(['password' => Hash::make($request->new_password)]);

        return response('', 200);
    }
}
