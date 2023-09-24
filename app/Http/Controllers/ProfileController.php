<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
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

    // Profile, Followers, Following
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
}
