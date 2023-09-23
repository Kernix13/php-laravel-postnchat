<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Follow;
use Illuminate\Http\Request;
use App\Events\OurExampleEvent;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    // Homepage
    public function showCorrectHomePage() {
        if (auth()->check()) {
            return view('homepage-feed', ['posts' => auth()->user()->feedPosts()->latest()->paginate(4)]);
        } else {
            $postCount = Cache::remember('postCount', 20, function () {
                return Post::count();
            });
            return view('homepage', ['postCount' => $postCount]);
        }
    }

    // Avatar
    public function storeAvatar(Request $request) {
        $request->validate([
            'avatar' => 'required|image|max:100'
        ]);
        $user = auth()->user();
        $filename = $user->username . '_' . $user->id . '_avatar_' . uniqid() . '.jpg';
        $imgData = Image::make($request->file('avatar'))->fit(175)->encode('jpg');

        Storage::put('public/avatars/' . $filename, $imgData);

        $oldAvatar = $user->avatar;
        $user->avatar = $filename;
        $user->save();

        if ($oldAvatar != "/fallback-avatar.jpg") {
            Storage::delete(str_replace("/storage/", "public/", $oldAvatar));
        }

        return back()->with('success', "Avatar updated.");
    }

    public function showAvatarForm() {
        return view('avatar-form');
    }

    // Register, Login, Logout
    public function register(Request $request) {
        $incomingFields = $request->validate([
            'username' => ['required', 'min:3', 'max:20', Rule::unique('users', 'username')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'min:8', 'confirmed']
        ]);
        $incomingFields['password'] = bcrypt($incomingFields['password']);
        
        $user = User::create($incomingFields);
        auth()->login($user);
        return redirect('/')->with('success', 'Account created!');
    }

    public function login(Request $request) {
        $incomingFields = $request->validate([
            'loginusername' => 'required',
            'loginpassword' => 'required'
        ]);

        if (auth()->attempt(['username' => $incomingFields['loginusername'], 'password' => $incomingFields['loginpassword']])) {
            $request->session()->regenerate();
            return redirect('/')->with('success', 'Welcome to your feed!');
        } else {
            return redirect('/')->with('failure', 'Invalid login');
        }

        return 'login page';
    }

    public function logout() {

        auth()->logout();
        return redirect('/')->with('success', 'You are now logged out!');
    }

    // Profile
    private function getSharedData($user) {
        $currentlyFollowing = 0;

        if (auth()->check()) {
            $currentlyFollowing = Follow::where([
                ['user_id', '=', auth()->user()->id],
                ['followeduser', '=', $user->id]
            ])->count();
        }

        View::share('sharedData', [
            'currentlyFollowing'    => $currentlyFollowing, 
            'avatar'                => $user->avatar, 
            'username'              => $user->username, 
            'postCount'             => $user->posts()->count(),
            'followerCount'         => $user->followers()->count(),
            'followingCount'        => $user->followingTheseUsers()->count()
        ]);
    }

    public function profile(User $user) {
        $this->getSharedData($user);
        return view('profile-posts', ['posts' => $user->posts()->latest()->get()]);
    }

    public function profileRaw(User $user) {
        return response()->json([
            'theHTML'   => view('profile-posts-only', ['posts' => $user->posts()->latest()->get()])->render(),
            'docTitle'  => $user->username . "'s Profile"
        ]);
    }

    public function profileFollowers(User $user) {
        $this->getSharedData($user);
        return view('profile-followers', ['followers' => $user->followers()->latest()->get()]);
    }

    public function profileFollowersRaw(User $user) {
        return response()->json([
            'theHTML'   => view('profile-followers-only', ['followers' => $user->followers()->latest()->get()])->render(),
            'docTitle'  => $user->username . "'s Followers"
        ]);
    }

    public function profileFollowing(User $user) {
        $this->getSharedData($user);
        return view('profile-following', ['following' => $user->followingTheseUsers()->latest()->get()]);
    }

    public function profileFollowingRaw(User $user) {
        return response()->json([
            'theHTML'   => view('profile-following-only', ['following' => $user->followingTheseUsers()->latest()->get()])->render(),
            'docTitle'  => "Who " . $user->username . " Follows"
        ]);
    }

    // API Token Requests
    public function loginApi(Request $request) {
        $incomingFields = $request->validate([
            'username' => 'required',
            'password' => 'required'
        ]);

        if (auth()->attempt($incomingFields)) {
            $user = User::where('username', $incomingFields['username'])->first();
            $token = $user->createToken('ourapptoken')->plainTextToken;
            return $token;
        }
        return 'Incorrect credentials.';
    }
    
}