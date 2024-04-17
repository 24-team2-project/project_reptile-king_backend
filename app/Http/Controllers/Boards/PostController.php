<?php

namespace App\Http\Controllers\Boards;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use App\Models\Category;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Upload\ImageController;


class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with('category')->paginate(10);
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
                'likes' => $post->likes,
                'views' => $post->views,
                'img_urls' => $post->img_urls,
            ];
        });
        return response()->json($posts);
    }

    public function selectCategory(Request $request)
    {
        $category = Category::findOrFail($request->category_id);

        if ($category['division'] == 'posts') {
            $subPostList = Category::where('parent_id', $request->category_id)->pluck('id');
            $posts = Post::whereIn('category_id', $subPostList)->with('category')->paginate(10);
        } else {
            $posts = Post::where('category_id', $request->category_id)->with('category')->orderBy('created_at', 'desc')->paginate(10);
        }

        $posts = $posts->map(function ($post) {
            return [
                'id' => $post->id,
                'title' => $post->title,
                'content' => $post->content,
                'comments' => $post->comments,
                'user_id' => $post->user_id,
                'category_id' => $post->category_id,
                'parent_id' => $post->parent_id,
                'category_name' => $post->category ? $post->category->name : '카테고리 없음',
                'created_at' => $post->created_at->toDateTimeString(),
                'updated_at' => $post->updated_at->toDateTimeString(),
                'likes' => $post->likes,
                'views' => $post->views,
                'img_urls' => $post->img_urls,
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
            'img_urls' => 'nullable|array',
            'img_urls.*' => 'nullable|string|url',
        ]);
        $reqData = $request->all();
        $reqData['user_id'] = $user->id;

        // 이미지 업로드 처리
        if ($request->has('img_urls')) {
            $images = new ImageController();
            $imageUrls = $images->uploadImageForController($reqData['images'], 'posts');
            $reqData['img_urls'] = $imageUrls;
        }

        $post = Post::create($reqData);

        return response()->json($post, 201);
    }


    public function show($id)
    {
        $post = Post::with('comments')->find($id);

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
            'img_urls' => 'nullable|array',
            'img_urls.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($post->user_id !== $user->id) {
            return response()->json(['message' => '이 글을 수정할 권한이 없습니다.'], 403);
        }

        $reqData = $request->all();
        $reqData['user_id'] = $user->id;

        $dbImgList = $post->img_urls;
        $updateImgList = $reqData['img_urls'];
        $deleteImgList = array_diff($dbImgList, $updateImgList);

        if (!empty($reqData['img_urls'])) {
            $images = new ImageController();
            $deleteResult = $images->deleteImages($deleteImgList);

            if(gettype($deleteResult) !== 'boolean'){
                return response()->json([
                    'msg' => '이미지 삭제 실패',
                    'error' => $deleteResult
                ], 500);
            }

            $imgUrls = $images->uploadImageForController($reqData['img_urls'], 'posts');
            $uploadImgList = array_merge($updateImgList, $imgUrls);
        }
        
        $post->update($reqData);

        return response()->json($post->fresh());
    }


    public function destroy(Post $post)
    {
        // $post = Post::find($post->id);
        // if (!$post) {
        //     return response()->json(['message' => '해당 게시글을 찾을 수 없습니다.'], 404);
        // }
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
                'likes' => $post->likes,
                'views' => $post->views,
                'img_urls' => $post->img_urls,
            ];
        });

        return response()->json($posts);
    }

    public function onClickLikesButton(Post $post)
    {
        $post->increment('likes');
    }

    public function myPost()
    {
        $user = JWTAuth::user();
        $posts = Post::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        return response()->json($posts);
    }
}
