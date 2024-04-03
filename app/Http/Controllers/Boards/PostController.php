<?php

namespace App\Http\Controllers\Boards;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        return response()->json($posts);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'title' => 'required',
            'content' => 'required',
            'category' => 'required',
            'img_urls' => 'sometimes|array',
            'img_urls.*' => 'string',
        ]);

        $post = Post::create($request->all());

        return response()->json($post, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {   
        $post = Post::with('comments')->findOrFail($post->id);
        if (!$post) {
            return response()->json(['message' => '해당 게시글을 찾을 수 없습니다.'], 404);
        }
        return response()->json($post);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        $post = Post::find($post->id);
        if (!$post) {
            return response()->json(['message' => '해당 게시글을 찾을 수 없습니다.'], 404);
        }

        $request->validate([
            'user_id' => 'required',
            'title' => 'required',
            'content' => 'required',
            'category' => 'required',
            'img_urls' => 'sometimes|array',
            'img_urls.*' => 'string',
        ]);

        $post->update($request->all());

        return response()->json($post);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post = Post::find($post->id);
        if (!$post) {
            return response()->json(['message' => '해당 게시글을 찾을 수 없습니다.'], 404);
        }

        $post->delete();

        return response()->json(['message' => '게시글이 삭제되었습니다.']);
    }

    public function search(Request $request)
    {
        $search = $request->query('search');

        if (empty($search)) {
            return response()->json(['message' => '검색어를 입력해주세요.'], 400);
        }

        $posts = Post::where('title', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%")
                    ->get();

        return response()->json($posts);
    }

    
}
