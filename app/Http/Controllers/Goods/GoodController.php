<?php

namespace App\Http\Controllers\Goods;

use App\Http\Controllers\Controller;
use App\Models\Good;
use App\Models\GoodReview;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Upload\ImageController;

class GoodController extends Controller
{
    public function index()
    {
        $goods = Good::with('category')
                    ->leftJoin('good_reviews', 'goods.id', '=', 'good_reviews.good_id')
                    ->selectRaw('goods.*, AVG(good_reviews.stars) as starAvg, COUNT(good_reviews.id) as reviewCount')
                    ->groupBy('goods.id')
                    ->get();

        $goods = $goods->map(function ($good) {
            return [
                'id' => $good->id,
                'name' => $good->name,
                'content' => $good->content,
                'price' => $good->price,
                'category_id' => $good->category_id,
                'category_name' => $good->category ? $good->category->name : '카테고리 없음',
                'created_at' => \Carbon\Carbon::parse($good->created_at)->toIso8601String(),
                'img_urls' => $good->img_urls,
                'reviewCount' => $good->reviewCount,
                'starAvg' => $good->starAvg,
            ];
        });

        return response()->json($goods);
    }

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
            'name' => 'required|string|max:50',
            'price' => 'required|numeric',
            'category_id' => 'required',
            'content' => 'required|string|max:255',
            'img_urls' => 'nullable|array',
            'img_urls.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $reqData = $request->all();

        // 이미지 업로드 처리
        if ($request->has('img_urls')) {
            $images = new ImageController();
            $imageUrls = $images->uploadImageForController($reqData['img_urls'], 'goods');
            $reqData['img_urls'] = $imageUrls;
        }

        $good = Good::create($reqData);

        return response()->json($good, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $good = Good::where('goods.id', $id)
        ->leftJoin('good_reviews', 'goods.id', '=', 'good_reviews.good_id')
        ->selectRaw('goods.*, AVG(good_reviews.stars) as starAvg, COUNT(good_reviews.id) as reviewCount')
        ->groupBy('goods.id')
        ->first();

        if (!$good) {
            return response()->json(['message' => '해당 상품을 찾을 수 없습니다.'], 404);
        }

        return response()->json($good);

        // $good->load('goodReviews');
        // $good = Good::with(['goodReviews'])
        //             ->where('id', $id)
        //             ->withCount('goodReviews as reviewCount')
        //             ->withAvg('goodReviews as starAvg', 'stars')
        //             ->first();
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Good $good)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Good $good)
    {
        $request->validate([
            'name' => 'required|string|max:50',
            'price' => 'required|numeric',
            'category_id' => 'required',
            'content' => 'required|string|max:255',
            'img_urls' => 'nullable|array',
            'img_urls.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $reqData = $request->all();
        $dbImgList = $good->img_urls;
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

            $imgUrls = $images->uploadImageForController($reqData['img_urls'], 'goods');
            $uploadImgList = array_merge($updateImgList, $imgUrls);
        }

        $good->update($reqData);

        return response()->json($good->fresh());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Good $good)
    {
        $good = Good::find($good->id);
        if (!$good) {
            return response()->json(['message' => '해당 상품을 찾을 수 없습니다.'], 404);
        }

        $good->delete();
        return response()->json(['message' => '상품 등록이 취소되었습니다.']);
    }

    public function search(Request $request) {
        $search = $request->query('search');

        if (empty($search)) {
            return response()->json(['message' => '검색어를 입력해주세요.'], 400);
        }

        $goods = Good::with('category', 'GoodReviews')
                    ->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%")
                    ->withCount('goodReviews as reviewCount')
                    ->withAvg('goodReviews as starAvg', 'stars')
                    ->get();

        $goods = $goods->map(function ($good) {
            return [
                'id' => $good->id,
                'name' => $good->name,
                'content' => $good->content,
                'price' => $good->price,
                'category_id' => $good->category_id,
                'category_name' => $good->category ? $good->category->name : '카테고리 없음',
                'created_at' => $good->created_at->toIso8601String(),
                'img_urls' => $good->img_urls,
                'reviewCount' => $good->reviewCount,
                'starAvg' => $good->starAvg,
            ];
        });

        return response()->json($goods);
    }
}
