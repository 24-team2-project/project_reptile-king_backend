<?php

namespace App\Http\Controllers\Reptiles;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Upload\ImageController;
use App\Http\Requests\ReptileRequest;
use App\Models\Reptile;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
class ReptileController extends Controller
{
    // 파충류 목록
    public function index()
    {
        $user = JWTAuth::user();

        try {
            $reptiles = $user->reptiles;

            if($reptiles->isEmpty()){
                return response()->json([
                    'msg' => '데이터 없음'
                ], 200);
            }

            return response()->json([
                'msg'      => '성공',
                'reptiles' => $reptiles
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'msg'   => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }

        
    }

    // 파충류 등록
    public function store(ReptileRequest $request)
    {
        $validatedList = [
            'nickname'  => ['required', 'string', 'max:255'],
            'species'   => ['required'],
            'gender'    => ['required', 'max:1', 'in:M,F'],
            'birth'     => [ 'nullable'],
            'memo'      => [ 'string', 'nullable'],
        ];
        if($request->hasFile('images')){
            $validatedList['images'] = ['nullable', 'array'];
            $validatedList['images.*'] = ['image', 'mimes:jpg,jpeg,png,bmp,gif,svg,webp', 'max:2048'];
        }

        $validator = Validator::make($request->all(), $validatedList);

        if($validator->fails()){
            return response()->json([
                'msg'   => '유효성 검사 오류',
                'error' => $validator->errors()
            ], 400);
        }

        $user = JWTAuth::user();
        $validator = $request->safe();
        
        try {
            $serialCode = 'REPTILE-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(2));
            while(Reptile::where('serial_code', $serialCode)->exists()){
                $serialCode = 'REPTILE-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(2));
            }

            $createList = [
                'user_id'       => $user->id,
                'serial_code'   => $serialCode,
                'nickname'      => $validator['nickname'],
                'species'       => $validator['species'],
                'gender'        => $validator['gender'],
                'birth'         => $validator['birth'],
                'memo'          => $validator['memo'],
                'img_urls'      => null,
            ];

            if($validator->has('images')){
                $images = new ImageController();
                $imageUrls = $images->uploadImageForController($validator['images'], 'reptiles');
                $createList['img_urls'] = $imageUrls;
            }

            Reptile::create($createList);

            return response()->json([
                'msg' => '등록 완료',
            ], 201);
            
        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }


    }

    // 파충류 정보 확인
    public function show(Reptile $reptile)
    {
        $user = JWTAuth::user();

        try {
            $msg = "성공";
            $state = 200;

            if(empty($reptile)){
                $msg = '데이터 없음';
                $state = 404;
            } else if($reptile->user_id !== $user->id){
                $msg = '권한 없음';
                $state = 403;
            } else if($reptile->expired_at !== null){
                $msg = '만료된 파충류';
                $state = 400;
            }

            return response()->json([
                'msg' => $msg,
                'reptile' => $reptile
            ], $state);

        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 파충류 정보 수정
    public function update(Request $request, Reptile $reptile)
    {
        $validatedList = [
            'nickname'  => ['required', 'string', 'max:255'],
            'species'   => ['required'],
            'gender'    => ['required', 'max:1', 'in:M,F'],
            'birth'     => [ 'nullable'],
            'memo'      => [ 'string', 'nullable'],
            'imgUrls'           => ['nullable', 'array'],
        ];
        if($request->hasFile('images')){
            $validatedList['images'] = ['nullable', 'array'];
            $validatedList['images.*'] = ['image', 'mimes:jpg,jpeg,png,bmp,gif,svg,webp', 'max:2048'];
        }

        $validator = Validator::make($request->all(), $validatedList);

        if($validator->fails()){
            return response()->json([
                'msg'   => '유효성 검사 오류',
                'error' => $validator->errors()
            ], 400);
        }

        $reqData = $validator->safe();

        $dbImgList = $reptile->img_urls;
        $updateImgList = $reqData['imgUrls'];
        $deleteImgList = array_diff($dbImgList, $updateImgList);

        $images = new ImageController();
        $deleteResult = $images->deleteImages($deleteImgList);

        if(gettype($deleteResult) !== 'boolean'){
            return $deleteResult;
        }

        if($reqData->has('newImages')){
            $imgUrls = $images->uploadImageForController($reqData['newImages'], 'reptiles');
            $uploadImgList = array_merge($updateImgList, $imgUrls);
        } else{
            $uploadImgList = $updateImgList;
        }

        $user = JWTAuth::user();

        if($reptile->user_id !== $user->id){
            return response()->json([
                'msg' => '권한 없음'
            ], 403);
        }

        try {
            $reptile->update([
                'nickname'  => $reqData['nickname'],
                'species'   => $reqData['species'],
                'gender'    => $reqData['gender'],
                'birth'     => $reqData['birth'],
                'memo'      => $reqData['memo'],
                'img_urls'  => $uploadImgList,
            ]);

            return response()->json([
                'msg' => '수정 완료'
            ], 200);
            
        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 파충류 정보 삭제
    public function destroy(Reptile $reptile)
    {
        $user = JWTAuth::user();

        if($reptile->user_id !== $user->id){
            return response()->json([
                'msg' => '권한 없음'
            ], 403);
        }

        try {

            $images = new ImageController();
            $deleteResult = $images->deleteImages($reptile->img_urls);
            if(gettype($deleteResult) !== 'boolean'){
                return $deleteResult;
            }

            $reptile->delete();

            return response()->json([
                'msg' => '삭제 완료'
            ], 200);
            
        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
