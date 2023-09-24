<?php

use App\Events\ChatMessage;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider and
| all of them will be assigned to the "web" middleware group. 
|
*/

/****** USER ROUTES ******/
// GET
Route::get('/', [UserController::class, "showCorrectHomePage"])->name('login');

// POST
Route::post('/register', [UserController::class, "register"])->middleware('guest');
Route::post('/login', [UserController::class, "login"])->middleware('guest');
Route::post('/logout', [UserController::class, "logout"])->middleware('mustBeLoggedIn');

/****** BLOG POST ROUTES ******/
// GET
Route::get('/create-post', [PostController::class, "showCreateForm"])->middleware('mustBeLoggedIn');
Route::get('/post/{post}', [PostController::class, "viewSinglePost"]);
Route::get('/post/{post}/edit', [PostController::class, "showEditForm"])->middleware('can:update,post');
Route::get('/search/{term}', [PostController::class, 'search']);
// POST
Route::post('/create-post', [PostController::class, "storeNewPost"])->middleware('mustBeLoggedIn');
// DELETE
Route::delete('/post/{post}', [PostController::class, "delete"])->middleware('can:delete,post');
// PUT
Route::put('/post/{post}', [PostController::class, "updatePost"])->middleware('can:update,post');

/****** FOLLOW ROUTES ******/
// POST
Route::post('/create-follow/{user:username}', [FollowController::class, "createFollow"])->middleware('mustBeLoggedIn');
Route::post('/remove-follow/{user:username}', [FollowController::class, "removeFollow"])->middleware('mustBeLoggedIn');

/****** PROFILE ROUTES ******/
// GET
Route::get('/manage-avatar', [ProfileController::class, "showAvatarForm"])->middleware('mustBeLoggedIn');
Route::get('/profile/{user:username}', [ProfileController::class, 'profile']);
Route::get('/profile/{user:username}/followers', [ProfileController::class, 'profileFollowers']);
Route::get('/profile/{user:username}/following', [ProfileController::class, 'profileFollowing']);

Route::middleware('cache.headers:public;max_age=20;etag')->group(function () {
  Route::get('/profile/{user:username}/raw', [ProfileController::class, 'profileRaw']);
  Route::get('/profile/{user:username}/followers/raw', [ProfileController::class, 'profileFollowersRaw']);
  Route::get('/profile/{user:username}/following/raw', [ProfileController::class, 'profileFollowingRaw']);
});
// POST
Route::post('/manage-avatar', [ProfileController::class, "storeAvatar"])->middleware('mustBeLoggedIn');

/****** ADMIN-ONLY ROUTES ******/
// GET
Route::get('admins-only', [AdminController::class, 'adminsOnly'])->middleware('can:visitAdminPages');

/****** CHAT ROUTES ******/
// POST
Route::post('/send-chat-message', [ChatController::class, 'sendChatMessage'])->middleware('mustBeLoggedIn');