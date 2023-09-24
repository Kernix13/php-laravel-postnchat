<?php

namespace App\Http\Controllers;

use App\Models\Post;

use Illuminate\Support\Str;
use Illuminate\Http\Request;

use App\Jobs\SendNewPostEmail;
// use Illuminate\Support\Facades\Mail;

class PostController extends Controller
{

    // Create, Save and View
    public function showCreateForm() {
        return view('create-post');
    }

    public function storeNewPost(Request $request) {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        $newPost = Post::create($incomingFields);

        dispatch(new SendNewPostEmail([
            'sendTo' => auth()->user()->email,
            'name'  => auth()->user()->username,
            'title' => $newPost->title
        ]));

        return redirect("/post/{$newPost->id}")->with('success', 'Post created!');
    }

    public function viewSinglePost(Post $post) {

        $post['body'] = strip_tags(Str::markdown($post->body), '<p><ol><ul><li><hr><br><img><h2><h3><h4><h5><h6><code><pre><ins><strong><em><del><blockquote><table><thead><tr><th><tbody><td><input>');

        return view('single-post', ['post' => $post]);
    }

    // Edit and Delete Posts
    public function showEditForm(Post $post) {
        return view('edit-post', ['post' => $post]);
    }

    public function updatePost(Post $post, Request $request) {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);

        $post->update($incomingFields);

        // return back()->with('success', 'Post updated.');
        return redirect("/post/{$post->id}")->with('success', 'Post updated.');
    }

    public function delete(Post $post) {
        
        $post->delete();

        return redirect('/profile/' . auth()->user()->username)->with('success', 'Post successfully deleted.');
    }

    // Search
    public function search($term) {
        $posts = Post::search($term)->get();
        $posts->load('user:id,username,avatar');
        return $posts;
    }

    // API Token Requests
    public function storeNewPostApi(Request $request) {
        $incomingFields = $request->validate([
            'title' => 'required',
            'body' => 'required'
        ]);

        $incomingFields['title'] = strip_tags($incomingFields['title']);
        $incomingFields['body'] = strip_tags($incomingFields['body']);
        $incomingFields['user_id'] = auth()->id();

        $newPost = Post::create($incomingFields);

        dispatch(new SendNewPostEmail(['sendTo' => auth()->user()->email, 'name' => auth()->user()->username, 'title' => $newPost->title]));

        return $newPost->id;
    }

    public function deleteApi(Post $post) {
        $post->delete();
        return 'true';
    }

}
