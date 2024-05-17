<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Models\Support;
use Illuminate\Http\Request;
use App\Models\Category;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Upload\ImageController;

class SupportController extends Controller
{
    public function index()
    {
        $user = JWTAuth::user();
        $supports = Support::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        return response()->json($supports);
    }

    public function show(Support $support)
    {
        return response()->json($support);
    }

    public function store(Request $request)
    {
        $user = JWTAuth::user();

        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'category_id' => 'required',
            'img_urls' => 'nullable|array',
        ]);
        $reqData = $request->all();
        $reqData['user_id'] = $user->id;

        // 이미지 업로드 처리
        if ($request->has('img_urls')) {
            $images = new ImageController();
        $imageUrls = $images->uploadImageForController($reqData['img_urls'], 'supports');
        $reqData['img_urls'] = $imageUrls;
        } else {
            $reqData['img_urls'] = [];
        }

        $support = Support::create($reqData);

        return response()->json($support, 201);
    }


    public function update(Request $request, Support $support)
    {
        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'category_id' => 'required',
            'img_urls' => 'nullable|array',
        ]);

        $reqData = $request->all();

        $dbImgList = $support->img_urls;
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

            $imgUrls = $images->uploadImageForController($reqData['img_urls'], 'supports');
            $uploadImgList = array_merge($updateImgList, $imgUrls);
        }

        $support->update($reqData);

        return response()->json($support->fresh());
    }

    public function destroy(Support $support)
    {
        $support->delete();
        return response()->json(['message' => '문의글이 삭제되었습니다.']);
    }

    // 문의 답변
    public function answer(Request $request, Support $support)
    {
        $request->validate([
            'answer' => 'required',
        ]);

        $reqData = $request->only('answer', 'answered_at');
        $support->update($reqData);

        return response()->json($support->fresh());
    }

    public function create()
    {
        //
    }

    public function edit(Support $support)
    {
        //
    }
}
