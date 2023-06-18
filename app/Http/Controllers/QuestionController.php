<?php

namespace App\Http\Controllers;

use App\Helpers\UserAuthorizer;
use App\Models\Question;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function addQuestion(Request $request) {
        $request->validate([
            'question' => 'required|string',
            'email' => 'required|email'
        ]);

        $user = UserAuthorizer::authorize($request);

        Question::create([
            'question' => $request->question,
            'email' => $request->email,
            'user_id' => $user ? $user->id : null
        ]);

        return response('', 200);
    }
}
