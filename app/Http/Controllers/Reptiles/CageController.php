<?php

namespace App\Http\Controllers\Reptiles;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Mqtt\MqttController;
use App\Http\Controllers\Upload\ImageController;
use App\Models\Cage;
use App\Models\CageSerialCode;
use App\Models\TemperatureHumidity;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class CageController extends Controller
{
    // 사육장 목록
    public function index()
    {
        $user = JWTAuth::user();

        try {
            $cages = $user->cages;

            if($cages->isEmpty()){
                return response()->json([
                    'msg' => '데이터 없음'
                ], 200);
            } else{
                return response()->json([
                    'msg'   => '성공',
                    'cages' => $cages
                ], 200);
            }

        } catch (Exception $e) {
            return response()->json([
                'msg'   => '서버 오류',
                'error' => $e->getMessage(),
            ], 500);
        }
        
    }

    // 사육장 등록
    public function store(Request $request)
    {
        $user = JWTAuth::user();

        $validatedList = [
            'name'              => ['required', 'string'],
            'reptileSerialCode' => ['nullable', 'string'],
            'memo'              => ['nullable', 'string'],
            'setTemp'           => ['required'],
            'setHum'            => ['required'],
            'serialCode'        => ['required', 'string'],
        ];
        if($request->hasFile('images')){
            $validatedList['images'] = ['nullable', 'array'];
            $validatedList['images.*'] = ['image', 'mimes:jpg,jpeg,png,bmp,gif,svg,webp', 'max:2048'];
        }
        
        $validator = Validator::make($request->all(), $validatedList);

        if($validator->fails()){
            return response()->json([
                'msg'   => '유효성 검사 오류',
                'error' => $validator->errors()->all(),
            ], 400);
        }
        
        $reqData = $validator->safe();

        try {
            // 파충류 등록 유무 확인
            if(!empty($reqData['reptileSerialCode'])){
                $cageConfirm = Cage::where('reptile_serial_code', $reqData['reptileSerialCode'])->first();
                if(!empty($cageConfirm) && $cageConfirm->expired_at === null){ 
                    return response()->json([
                        'msg' => '이미 등록된 파충류',
                    ], 400);
                }
            } else{
                $reqData['reptileSerialCode'] = null;
            }

            // 일련번호 등록 유무 확인
            $CageDoubleCheck = Cage::where('serial_code', $reqData['serialCode'])->first();
            if(!empty($CageDoubleCheck)){
                return response()->json([
                    'msg' => '이미 등록된 사육장',
                ], 400);
            }

            $serialCodeConfirm = CageSerialCode::where('serial_code', $reqData['serialCode'])->first();
            // 일련번호 확인
            if(empty($serialCodeConfirm)){
                return response()->json([
                    'msg' => '일련번호를 찾을 수 없음',
                ], 400);
            } else{
                
                $createList = [
                    'user_id'             => $user->id,
                    'name'                => $reqData['name'],
                    'reptile_serial_code' => $reqData['reptileSerialCode'],
                    'memo'                => $reqData['memo'],
                    'set_temp'            => $reqData['setTemp'],
                    'set_hum'             => $reqData['setHum'],
                    'serial_code'         => $reqData['serialCode'],
                    'img_urls'            => null,
                ];

                if($reqData->has('images')){
                    $images = new ImageController();
                    $imgUrls = $images->uploadImageForController($reqData['images'], 'cages');
                    $createList['img_urls'] = $imgUrls;
                }

                Cage::create($createList);

                // MQTT 전송
                $result = $this->transmitTempHumData($reqData['serialCode'], $reqData['setTemp'], $reqData['setHum']);
                if(gettype($result) !== 'boolean'){
                    return $result;
                }

                return response()->json([
                    'msg' => '등록 완료',
                ], 201);
            }

        } catch (Exception $e) {
            return response()->json([
                'msg'   => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    // 사육장 정보
    public function show(Cage $cage)
    {
        $user = JWTAuth::user();

        try {
            if(empty($cage)){
                return response()->json([
                    'msg' => '데이터 없음'
                ], 200);
            } else if($cage->user_id !== $user->id){
                return response()->json([
                    'msg' => '권한 없음'
                ], 403);
            } else if($cage->expired_at !== null){
                return response()->json([
                    'msg' => '만료된 데이터'
                ], 410);
            } else{
                return response()->json([
                    'msg' => '성공',
                    'cage' => $cage
                ], 200);
            }

        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }
        
    }

    // 사육장 정보 수정
    public function update(Request $request, Cage $cage)
    {
        $user = JWTAuth::user();

        if($cage->user_id !== $user->id){
            return response()->json([
                'msg' => '권한 없음'
            ], 403);
        } else if($cage->expired_at !== null){
            return response()->json([
                'msg' => '만료된 데이터'
            ], 410);
        } else {
            $validatedList = [
                'name'              => ['required', 'string', 'max:255'],
                'reptileSerialCode' => ['nullable', 'string'],
                'memo'              => ['nullable', 'string'],
                'serialCode'        => ['required', 'string'],
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
                    'error' => $validator->errors()->all(),
                ], 400);
            }
            
            $reqData = $validator->safe();

            try {
                if(!empty($reqData['reptileSerialCode'])){
                    $cageConfirm = Cage::where('reptile_serial_code', $reqData['reptileSerialCode'])->first();
                    if(!empty($cageConfirm) && $cageConfirm->expired_at === null){ 
                        return response()->json([
                            'msg' => '이미 등록된 파충류',
                        ], 400);
                    }
                } else{
                    $reqData['reptileSerialCode'] = null;
                }
        
                $dbImgList = $cage->img_urls;
                $updateImgList = $reqData['imgUrls'];
        
                if(empty($dbImgList)){
                    $dbImgList = [];
                }
                $deleteImgList = array_diff($dbImgList, $updateImgList);
                
                $images = new ImageController();
                if(!empty($deleteImgList)){
                    $deleteResult = $images->deleteImages($deleteImgList);
                    if(gettype($deleteResult) !== 'boolean'){
                        return $deleteResult;
                    }
                }
        
                if($reqData->has('images')){
                    $imgUrls = $images->uploadImageForController($reqData['images'], 'cages');
                    $uploadImgList = array_merge($updateImgList, $imgUrls);
                } else{
                    $uploadImgList = $updateImgList;
                }

                $cage->update([
                    'name'                => $reqData['name'],
                    'reptile_serial_code' => $reqData['reptileSerialCode'],
                    'memo'                => $reqData['memo'],
                    'img_urls'            => empty($uploadImgList) ? null : $uploadImgList,
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
    }

    // 사육장 정보 삭제
    public function destroy(Cage $cage)
    {
        $user = JWTAuth::user();

        if($cage->user_id !== $user->id){
            return response()->json([
                'msg' => '권한 없음'
            ], 403);
        } else if($cage->expired_at !== null){
            return response()->json([
                'msg' => '만료된 데이터'
            ], 410);
        } else{
            try {
                // 이미지 삭제
                if($cage->img_urls !== null){
                    $images = new ImageController();
                    $deleteResult = $images->deleteImages($cage->img_urls);
                    if(gettype($deleteResult) !== 'boolean'){
                        return $deleteResult;
                    }
                }
    
                // $cage->update([
                //     'expired_at' => now()
                // ]);
    
                $cage->delete();
    
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

    // 온습도 데이터 전달(프론트에서 사용)
    public function getTempHumData(String $serialCode)
    {
        $user = JWTAuth::user();

        try {
            $cage = Cage::where([
                ['serial_code', $serialCode],
                ['user_id', $user->id],
                ['expired_at', null]
            ])->first();
    
            if(empty($cage)){
                return response()->json([
                    'msg' => '사육장을 찾을 수 없음'
                ], 404);
            }

            $tempHumData = TemperatureHumidity::where('serial_code', $cage->serial_code)->get();

            return response()->json([
                'msg' => '성공',
                'data' => $tempHumData
            ], 200);
            
        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 온습도 데이터 수정(프론트에서 사용)
    public function updateTempHumData(Request $request, Cage $cage)
    {
        $user = JWTAuth::user();

        if(empty($cage)){
            return response()->json([
                'msg' => '사육장을 찾을 수 없음'
            ], 404);
        }else if($cage->user_id !== $user->id){
            return response()->json([
                'msg' => '권한 없음'
            ], 403);
        } else if($cage->expired_at !== null){
            return response()->json([
                'msg' => '만료된 데이터'
            ], 410);
        } else{
            $validatedList = [
                'temperature' => ['required'],
                'humidity'    => ['required'],
            ];
    
            $validator = Validator::make($request->all(), $validatedList);
    
            if($validator->fails()){
                return response()->json([
                    'msg'   => '유효성 검사 오류',
                    'error' => $validator->errors()->all(),
                ], 400);
            }
    
            $reqData = $validator->safe();
    
            try {
                $cage->update([
                    'set_temp' => $reqData['setTemp'],
                    'set_hum'  => $reqData['setHum'],
                ]);
    
                // MQTT 전송
                $result = $this->transmitTempHumData($cage->serial_code, $reqData['setTemp'], $reqData['setHum']);
                if(gettype($result) !== 'boolean'){
                    return $result;
                }
    
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
    }

    // 설정 온, 습도 하드웨어로 전달
    public function transmitTempHumData($serialCode, $setTemp, $setHum)
    {
        $mqtt = new MqttController();
        $result = $mqtt->sendData($serialCode, $setTemp, $setHum);
        return $result;
    }
}