<?php

namespace App\Http\Controllers\Boards;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use Tymon\JWTAuth\Facades\JWTAuth;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::all();
        return response()->json($posts);
    }

    public function selectPost(Request $request)
    {   
        $category = $request->category;
        $selectPost = Post::where('category', $category)->orderBy('created_at', 'desc')->get();
        return response()->json($selectPost);
    }

    
    public function create()
    {
        //
    }

    
    public function store(Request $request)
    {   
        $user = JWTAuth::user();

        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'category' => 'required',
            // 미구현
            // 'img_urls' => 'sometimes|array',
            // 'img_urls.*' => 'string',
        ]);
        $data = $request->only(['title', 'content', 'category']);
        $data['user_id'] = $user->id;
        $post = Post::create($data);

        return response()->json($post, 201);
    }

    
    public function show(Post $post)
    {   
        $post = Post::with('comments')->find($post->id);
        if (!$post) {
            return response()->json(['message' => '해당 게시글을 찾을 수 없습니다.'], 404);
        }
        $post->increment('views');
        return response()->json($post);
    }

    
    public function edit(Post $post)
    {
        //
    }

    
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
            // 미구현
            // 'img_urls' => 'sometimes|array',
            // 'img_urls.*' => 'string',
        ]);

        $post->update($request->all());

        return response()->json($post);
    }

    
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

    public function onClickLikesButton(Post $post)
    {
        $post->increment('likes');
    }
}
