<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use App\Models\Follow;

use Illuminate\Http\Request;

use App\Events\OurExampleEvent; // this should be removed

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

    // Profile: in ProfileController now

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
