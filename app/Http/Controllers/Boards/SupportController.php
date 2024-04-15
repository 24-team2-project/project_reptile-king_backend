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
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = JWTAuth::user();
        $supports = Support::where('user_id', $user->id)->get();
        return response()->json([$supports]);
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
        $user = JWTAuth::user();

        $request->validate([
            'title' => 'required',
            'content' => 'required',
            'category_id' => 'required',
            'img_urls' => 'nullable|array',
            'img_urls.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'answer' => 'nullable',
            'answered_at' => 'nullable',
        ]);
        $reqData = $request->safe();
        $reqData['user_id'] = $user->id;

        // 이미지 업로드 처리
        $images = new ImageController();
        $imageUrls = $images->uploadImageForController($reqData['images'], 'posts');
        $reqData['img_urls'] = $imageUrls;

        $support = Support::create($reqData);

        return response()->json($support, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Support $support)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Support $support)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Support $support)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Support $support)
    {
        //
    }
}
