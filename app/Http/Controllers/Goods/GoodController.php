<?php

namespace App\Http\Controllers\Goods;

use App\Http\Controllers\Controller;
use App\Models\Good;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class GoodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $goods = Good::all();
        return response()->json($goods);
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
            'name' => 'required|string|max:50',
            'price' => 'required|numeric',
            'category' => 'required',
            'content' => 'required|string|max:255',
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

        $good = Good::create($requestData);
        return response()->json($good, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Good $good)
    {
        $good = Good::with('reviews')->find($id);
        $post = Post::with('comments')->find($id);
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
        $good = Good::find($id);
    if (!$good) {
        return response()->json(['message' => '해당 상품을 찾을 수 없습니다.'], 404);
    }

    $validator = Validator::make($request->all(), [
        'name' => 'required|string|max:50',
        'price' => 'required|numeric',
        'category' => 'required',
        'content' => 'required|string|max:255',
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

    $good->update($requestData);
    return response()->json($good);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Good $good)
    {
        $good = Good::find($id);
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

        return response()->json($posts);
    }
}
