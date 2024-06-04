<?php

namespace App\Http\Controllers\Reptiles;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Upload\ImageController;
use App\Http\Controllers\Users\AlarmController;
use App\Models\Alarm;
use App\Models\Reptile;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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

            $state = 200;
            $jsonData = [ 'msg' => '성공' ];

            if($reptiles->isEmpty()){
                $state = 204;
                $jsonData['msg'] = '데이터 없음';
            } else{
                $jsonData['reptiles'] = $reptiles;
            }

            return response()->json($jsonData, $state);

        } catch (Exception $e) {
            return response()->json([
                'msg'   => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    // 파충류 등록
    public function store(Request $request)
    {
        $user = JWTAuth::user();

        $validatedList = [
            'name'      => ['required', 'string', 'max:255'],
            'species'   => ['required'],
            'gender'    => ['required', 'max:1', 'in:M,F'],
            'birth'     => [ 'nullable'],
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
        
        try {
            $serialCode = 'REPTILE-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(2));
            while(Reptile::where('serial_code', $serialCode)->exists()){
                $serialCode = 'REPTILE-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(4)).'-'.Str::upper(Str::random(2));
            }

            $createList = [
                'user_id'       => $user->id,
                'serial_code'   => $serialCode,
                'name'          => $reqData['name'],
                'species'       => $reqData['species'],
                'gender'        => $reqData['gender'],
                'birth'         => $reqData['birth'],
                'img_urls'      => [],
            ];

            if($reqData->has('images')){
                $images = new ImageController();
                $imageUrls = $images->uploadImageForController($reqData['images'], 'reptiles');
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
    public function show(String $reptileSerialCode)
    {
        $user = JWTAuth::user();

        try {
            $reptile = Reptile::where([
                ['user_id', $user->id],
                ['serial_code', $reptileSerialCode]
            ])->first();

            if(empty($reptile)){
                return response()->json([
                    'msg' => '데이터 없음'
                ], 204);
            } else if($reptile->user_id !== $user->id){
                return response()->json([
                    'msg' => '권한 없음'
                ], 403);
            } else if($reptile->expired_at !== null){
                return response()->json([
                    'msg' => '만료된 데이터'
                ], 410);
            }

            return response()->json([
                'msg' => '성공',
                'reptile' => $reptile
            ], 200);

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
        $user = JWTAuth::user();

        if($reptile->user_id !== $user->id){
            return response()->json([
                'msg' => '권한 없음'
            ], 403);
        }

        $validatedList = [
            'name'      => ['required', 'string', 'max:255'],
            'species'   => ['required'],
            'gender'    => ['required', 'max:1', 'in:M,F'],
            'birth'     => [ 'nullable'],
            'imgUrls'   => ['nullable' , 'string'],
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
        $updateImgList = json_decode($reqData['imgUrls']);
        
        $deleteImgList = array_diff($dbImgList, $updateImgList);

        $images = new ImageController();
        if(!empty($deleteImgList)){
            $deleteResult = $images->deleteImages($deleteImgList);
            if(gettype($deleteResult) !== 'boolean'){
                return $deleteResult;
            }
        }

        if($reqData->has('images')){
            $imgUrls = $images->uploadImageForController($reqData['images'], 'reptiles');
            $uploadImgList = array_merge($updateImgList, $imgUrls);
            $uploadImgList = collect($uploadImgList)->flatten()->all();
        } else{
            $uploadImgList = $updateImgList;
        }

        try {
            $reptile->update([
                'name'      => $reqData['name'],
                'species'   => $reqData['species'],
                'gender'    => $reqData['gender'],
                'birth'     => $reqData['birth'],
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

            if(!empty($reptile->img_urls)){
                $images = new ImageController();
                $deleteResult = $images->deleteImages($reptile->img_urls);
                if(gettype($deleteResult) !== 'boolean'){
                    return $deleteResult;
                }
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

    // 파충류 분양
    public function sellReptile(Request $request)
    {
        $user = JWTAuth::user();

        $validatedList = [
            'receiveNickname' => ['required', 'string', 'max:255'],
            // 고민중
        ];

        $validator = Validator::make($request->all(), $validatedList);

        if($validator->fails()){
            return response()->json([
                'msg'   => '유효성 검사 오류',
                'error' => $validator->errors()
            ], 400);
        }

        $reqData = $validator->safe();

        try {
            $receiveUser = User::where('nickname', $reqData['receiveNickname'])->first();
            if(empty($receiveUser)){
                return response()->json([
                    'msg' => '유저 없음'
                ], 204);
            }

            $receiveData = [
                'user_id'   => $receiveUser->id, // 받는 사람의 아이디
                'category'  => 'reptile_sales_receive',
                'title'     => '파충류 분양 신청',
                'content'   => $user->nickname.' 유저가 파충류 분양을 신청하였습니다.',
                'readed'    => false,
                'sened_user_id' => $user->id,
                'img_urls'  => [],
            ];
            
            // 분양 알림 전송
            $alarm = new AlarmController();
            $result = $alarm->sendAlarm('user', $receiveData);

            return response()->json([
                    'msg' => $result['msg']
                ], $result['status']);
            
        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }
    }



}
