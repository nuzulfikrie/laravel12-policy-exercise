<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class PostNoPolicyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user) {
            $posts = Post::where('user_id', $user->id)->get();
        } else {
            $posts = Post::all();
        }

        return view('posts.index', compact('posts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        return view('posts.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post = Post::create($request->all());
        return redirect()->route('posts.index')->with('success', 'Post created successfully');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Post $post): View
    {

        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        //check if the post is owned by the user
        if ($post->user_id !== $user->id) {
            abort(403);
        }

        return view('posts.show', compact('post'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Post $post): View
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        //check if the post is owned by the user
        if ($post->user_id !== $user->id) {
            abort(403, 'You are not allowed to edit this post');
        }

        return view('posts.edit', compact('post'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post): RedirectResponse
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        //check if the post is owned by the user
        if ($post->user_id !== $user->id) {
            abort(403, 'You are not allowed to edit this post');
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $post->update($request->all());
        return redirect()->route('posts.index')->with('success', 'Post updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Post $post): RedirectResponse
    {
        $user = $request->user();

        if (!$user) {
            abort(403);
        }

        //check if the post is owned by the user
        if ($post->user_id !== $user->id) {
            abort(403, 'You are not allowed to delete this post');
        }
        $post->delete();
        return redirect()->route('posts.index')->with('success', 'Post deleted successfully');
    }
}
