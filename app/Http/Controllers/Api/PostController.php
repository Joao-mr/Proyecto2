<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('user')->get();
        return $posts;
    }


    public function show(Post $post)
    {
        $post->load('user:id,name,surname1', 'categories');
        return $post;
    }


    public function destroy(Post $post)
    {
        $post->delete();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255', 'min:2'],  
            'content' => ['required', 'string', 'min:2'],
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'user_id' => ['required', 'integer']
        ]);

        $post = Post::create($data);
        $post->categories()->attach($data['categories']);
        return $post;
    }
}
