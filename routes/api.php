<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DefaultCollectionController;
use App\Http\Controllers\FilmController;
use App\Http\Controllers\FollowerController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SaveController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\UserSettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/restore/password', [AuthController::class, 'restorePassword']);
Route::post('/set/new/password', [AuthController::class, 'setNewPassword']);

Route::post('/add/question', [QuestionController::class, 'addQuestion']);

Route::get('/get/user', [UserController::class, 'getUser']);
Route::get('/get/subscribers', [FollowerController::class, 'getSubscribers']);
Route::get('/get/subscriptions', [FollowerController::class, 'getSubscriptions']);

Route::get('/get/default/collection', [DefaultCollectionController::class, 'getDefaultCollection']);
Route::get('/get/default/collections', [DefaultCollectionController::class, 'getDefaultCollections']);
Route::get('/get/famous/collections', [CollectionController::class, 'getFamousCollections']);
Route::get('/get/famous/users', [UserController::class, 'getFamousUsers']);
Route::get('/get/collection', [CollectionController::class, 'getCollection']);
Route::get('/get/collections', [CollectionController::class, 'getCollections']);
Route::get('/get/films', [FilmController::class, 'getFilms']);
Route::get('/get/saves', [SaveController::class, 'getSaves']);

Route::get('/search/all', [SearchController::class, 'searchAll']);
Route::get('/search/films', [SearchController::class, 'searchFilms']);
Route::get('/search/collections', [SearchController::class, 'searchCollections']);
Route::get('/search/users', [SearchController::class, 'searchUsers']);

Route::post('/google/auth', [AuthController::class, 'googleAuth']);


Route::group(['middleware' => ['auth:sanctum']], function () {
    Route::get('/auth', [AuthController::class, 'auth']);

    Route::post('/add/notification/token', [NotificationController::class, 'addNotificationToken']);

    Route::post('/toggle/subscribe/notifications', [UserSettingsController::class, 'toggleSubscribeNotifications']);
    Route::post('/toggle/like/notifications', [UserSettingsController::class, 'toggleLikeNotifications']);

    Route::post('/edit/user/data', [UserController::class, 'editUserData']);
    Route::post('/edit/email', [UserController::class, 'editEmail']);
    Route::post('/change/user/password', [UserController::class, 'changePassword']);

    Route::post('/toggle/subscription', [FollowerController::class, 'toggleSubscription']);

    Route::get('/get/all/collections', [CollectionController::class, 'getAllCollections']);
    Route::get('/get/nav/collections', [CollectionController::class, 'getNavCollections']);
    Route::post('/create/collection', [CollectionController::class, 'createCollection']);
    Route::post('/edit/collection', [CollectionController::class, 'editCollection']);
    Route::delete('/delete/collection', [CollectionController::class, 'deleteCollection']);
    Route::post('/toggle/collection/public', [CollectionController::class, 'toggleCollectionPublic']);

    Route::post('/toggle/favorite', [FilmController::class, 'toggleFavorite']);
    Route::post('/toggle/film', [CollectionController::class, 'toggleFilm']);
    Route::post('/toggle/save', [SaveController::class, 'toggleCollection']);

    Route::post('/set/order', [FilmController::class, 'setOrder']);

});
