<?php

namespace App\Http\Controllers\Goods;

use App\Http\Controllers\Controller;
use App\Models\Good;
use App\Models\GoodReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Upload\ImageController;

class GoodController extends Controller
{
    public function index()
    {
        $goods = Good::leftJoin('good_reviews', 'goods.id', '=', 'good_reviews.good_id')
                    ->selectRaw('goods.*, AVG(good_reviews.stars) as starAvg, COUNT(good_reviews.id) as reviewCount')
                    ->groupBy('goods.id')
                    ->get();

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
            $imageUrls = $images->uploadImageForController($reqData['images'], 'goods');
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
        // $good->load('goodReviews');
        $good = Good::with('goodReviews')->find($id);

        if (!$good) {
            return response()->json(['message' => '해당 상품을 찾을 수 없습니다.'], 404);
        }

        return response()->json($good);
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
        $good = Good::find($request->id);
    if (!$good) {
        return response()->json(['message' => '해당 상품을 찾을 수 없습니다.'], 404);
    }

    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:50',
        'price' => 'required|numeric',
        'category_id' => 'required',
        'content' => 'required|string|max:255',
        'img_urls' => 'nullable|array',
        'img_urls.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $requestData = $request->all();
    if(isset($requestData['img_urls'])) {
        $requestData['img_urls'] = json_encode($requestData['img_urls']);
    }

    $good->update($requestData);
    return response()->json($good);
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

        $goods = Good::where('name', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%")
                    ->get();

        return response()->json($goods);
    }
}
