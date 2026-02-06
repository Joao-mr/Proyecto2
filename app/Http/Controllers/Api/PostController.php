<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Models\Post;

use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        // return "Hello world";
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
        // $this->authorize('post-edit');


        $data = $request->validate([
            'title' => ['required', 'string', 'max:255', 'min:2'],  
            'content' => ['required', 'string', 'min:2'],
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id',
            'user_id' => ['required', 'integer']
        ]);


        // $data['user_id'] =  auth()->user()->id;
        $post = Post::create($data);
        $post->categories()->attach($data['categories']);
        return $post;
    }
}
