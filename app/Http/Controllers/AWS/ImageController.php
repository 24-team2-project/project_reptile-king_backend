<?php

namespace App\Http\Controllers\AWS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;


class ImageController extends Controller
{
    public function uploadImage(Request $request){
        $validated = Validator::make($request->all(), [
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'division' => ['required', 'string'],
        ]);

        if ($validated->fails()) {
            return response()->json([
                'msg' => '이미지 요청이 올바르지 않습니다.',
                'error' => $validated->errors()
            ], 400);
        }

        $image = $validated->validated()['image'];
        $division = $validated->validated()['division'];

        $imageName = uniqid('image_').'.'.$image->getClientOriginalName();

        $uploadPath = 'images/'.$division;

        $image->storeAs($uploadPath, $imageName, 's3');

    



        
    }
}
