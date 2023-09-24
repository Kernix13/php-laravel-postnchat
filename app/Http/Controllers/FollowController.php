<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Follow;

use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function createFollow(User $user) {
        // You cannot follow yourself:
        if ($user->id == auth()->user()->id) {
            return back()->with('failure', 'You cannot follow yourself.');
        }

        // You cannot follow someone you are already following:
        // returns 1 or 0
        $existCheck = Follow::where([['user_id', '=', auth()->user()->id], ['followeduser', '=', $user->id]])->count();

        if ($existCheck) {
            return back()->with('failure', 'You are already following that user.');
        }

        $newfollow = new Follow;
        $newfollow->user_id = auth()->user()->id;
        $newfollow->followeduser = $user->id;
        $newfollow->save();

        return back()->with('success', 'User followed!');
    }

    public function removeFollow(User $user) {
        Follow::where([
            ['user_id', '=', auth()->user()->id],
            ['followeduser', '=', $user->id]
        ])->delete();
        
        return back()->with('success', 'User unfollowed.');
    }
}
