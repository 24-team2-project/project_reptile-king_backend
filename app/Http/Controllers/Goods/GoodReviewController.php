<?php

namespace App\Http\Controllers\Goods;

use App\Http\Controllers\Controller;
use App\Models\GoodReview;
use Illuminate\Http\Request;

class GoodReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reviews = GoodReview::all();
        return response()->json($reviews);
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
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|integer',
            'good_id' => 'required|integer',
            'summary' => 'required|string|max:255',
            'content' => 'required|string',
            'stars' => 'required|integer|min:1|max:5',
            'img_urls' => 'nullable|array',
            'img_urls.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $requestData = $request->all();
        if(isset($requestData['img_urls'])) {
            $requestData['img_urls'] = json_encode($requestData['img_urls']);
        }

        $review = GoodReview::create($requestData);
        return response()->json($review, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(GoodReview $goodReview)
    {
        $review = GoodReview::find($id);
        if (!$review) {
            return response()->json(['message' => '해당 리뷰를 찾을 수 없습니다.'], 404);
        }
        return response()->json($review);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(GoodReview $goodReview)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, GoodReview $goodReview)
    {
        $review = GoodReview::find($id);
        if (!$review) {
            return response()->json(['message' => '해당 리뷰를 찾을 수 없습니다.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'summary' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
            'stars' => 'sometimes|required|integer|min:1|max:5',
            'img_urls' => 'nullable|array',
            'img_urls.*' => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $requestData = $request->all();
        if(isset($requestData['img_urls'])) {
            $requestData['img_urls'] = json_encode($requestData['img_urls']);
        }

        $review->update($requestData);
        return response()->json($review);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(GoodReview $goodReview)
    {
        $review = GoodReview::find($id);
        if (!$review) {
            return response()->json(['message' => '해당 리뷰를 찾을 수 없습니다.'], 404);
        }

        $review->delete();
        return response()->json(['message' => '리뷰가 삭제되었습니다.']);
    }
}
