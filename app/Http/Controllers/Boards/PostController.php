<?php

namespace App\Http\Controllers\Boards;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Category;
use Tymon\JWTAuth\Facades\JWTAuth;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('category')->get();
        $posts = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'user_id' => $post->user_id,
                'category_id' => $post->category_id,
                'parent_id' => $post->parent_id,
                'category_name' => $post->category ? $post->category->name : '카테고리 없음',
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
            ];
        });
        return response()->json($posts);
    }

    public function selectCategory(Request $request)
    {
        $category = $request->category_id;
        $posts = Post::where('category_id', $category)->orderBy('created_at', 'desc')->get();
        $posts = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'user_id' => $post->user_id,
                'category_id' => $post->category_id,
                'category_name' => $post->category ? $post->category->name : '카테고리 없음',
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
            ];
        });
        return response()->json($posts);
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
            'category_id' => 'required',
            // 미구현
            // 'img_urls' => 'sometimes|array',
            // 'img_urls.*' => 'string',
            // images => 'sometimes|array',
        ]);
        $data = $request->only(['title', 'content', 'category_id']);
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
        $user = JWTAuth::user();

        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'category_id' => 'required',
            // 미구현
            // 'img_urls' => 'sometimes|array',
            // 'img_urls.*' => 'string',
        ]);

        if ($post->user_id !== $user->id) {
            return response()->json(['message' => '이 글을 수정할 권한이 없습니다.'], 403);
        }

        $data = $request->only(['title', 'content', 'category_id']);
        $data['user_id'] = $user->id;
        $post->update($data);

        return response()->json($post->fresh());
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

        if ($posts->isEmpty()) {
            return response()->json(['message' => '검색 결과가 없습니다.'], 404);
        }

        $posts = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'user_id' => $post->user_id,
                'category_id' => $post->category_id,
                'category_name' => $post->category ? $post->category->name : '카테고리 없음',
                'created_at' => $post->created_at,
                'updated_at' => $post->updated_at,
            ];
        });

        return response()->json($posts);
    }

    public function onClickLikesButton(Post $post)
    {
        $post->increment('likes');
    }
}
