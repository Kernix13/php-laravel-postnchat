<?php

use App\Events\ChatMessage;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\PostController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\FollowController;

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
Route::get('/manage-avatar', [UserController::class, "showAvatarForm"])->middleware('mustBeLoggedIn');
// POST
Route::post('/register', [UserController::class, "register"])->middleware('guest');
Route::post('/login', [UserController::class, "login"])->middleware('guest');
Route::post('/logout', [UserController::class, "logout"])->middleware('mustBeLoggedIn');
Route::post('/manage-avatar', [UserController::class, "storeAvatar"])->middleware('mustBeLoggedIn');

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
Route::get('/profile/{user:username}', [UserController::class, 'profile']);
Route::get('/profile/{user:username}/followers', [UserController::class, 'profileFollowers']);
Route::get('/profile/{user:username}/following', [UserController::class, 'profileFollowing']);

Route::middleware('cache.headers:public;max_age=20;etag')->group(function () {
  Route::get('/profile/{user:username}/raw', [UserController::class, 'profileRaw']);
  Route::get('/profile/{user:username}/followers/raw', [UserController::class, 'profileFollowersRaw']);
  Route::get('/profile/{user:username}/following/raw', [UserController::class, 'profileFollowingRaw']);
});


/****** ADMIN-ONLY ROUTES ******/
// GET
Route::get('admins-only', [AdminController::class, 'adminsOnly'])->middleware('can:visitAdminPages');

/****** CHAT ROUTES ******/
// POST
Route::post('/send-chat-message', [ChatController::class, 'sendChatMessage'])->middleware('mustBeLoggedIn');