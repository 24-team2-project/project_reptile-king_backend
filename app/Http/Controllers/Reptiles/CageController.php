<?php

namespace App\Http\Controllers\Reptiles;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Mqtt\MqttController;
use App\Http\Controllers\Upload\ImageController;
use App\Http\Controllers\Users\AlarmController;
use App\Models\Alarm;
use App\Models\Cage;
use App\Models\CageSerialCode;
use App\Models\TemperatureHumidity;
use App\Models\User;
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

            $state = 200;
            $jsonData = ['msg' => '성공'];

            if($cages->isEmpty()){
                $jsonData['msg'] = '데이터 없음';
                $state = 204;
            } else{
                $jsonData['cages'] = $cages;
            }

            return response()->json($jsonData, $state);

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
                    'set_temp'            => $reqData['setTemp'],
                    'set_hum'             => $reqData['setHum'],
                    'serial_code'         => $reqData['serialCode'],
                    'img_urls'            => [],
                ];

                if($reqData->has('images')){
                    $images = new ImageController();
                    $imgUrls = $images->uploadImageForController($reqData['images'], 'cages');
                    $createList['img_urls'] = $imgUrls;
                }

                Cage::create($createList);

                $latestCage = $user->cages()->latest()->first();

                // MQTT 전송
                $result = $this->transmitTempHumData($reqData['serialCode'], $reqData['setTemp'], $reqData['setHum']);
                if(gettype($result) !== 'boolean'){
                    return $result;
                }

                if(!($user->fcmTokens->isEmpty())){
                    $alarm = new AlarmController();
    
                    $receiveData = [
                        'user_id'   => $user->id,
                        'category'  => 'cages',
                        'category_id' => $latestCage->id,
                        'title'     => '사육장 등록',
                        'content'   => '사육장 등록이 완료되었습니다.',
                        'readed'    => false,
                        'img_urls'  => [],
                        'created_at' => now()->toDateTimeString(),
                    ];
        
                    $result = $alarm->sendAlarm($receiveData);
                    
                    if($result['flag'] === false){
                        return response()->json([
                            'msg' => $result['msg']
                        ], $result['status']);
                    }   

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
                ], 204);
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
                'serialCode'        => ['required', 'string'],
                'imgUrls'           => ['nullable', 'string'],
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
                    $cageConfirm = Cage::where('user_id', '!=', $user->id)
                                    ->where('reptile_serial_code', '=', $reqData['reptileSerialCode'])
                                    ->whereNull('expired_at')->get();
                    if($cageConfirm->isNotEmpty()){ 
                        return response()->json([
                            'msg' => '이미 등록된 파충류',
                        ], 400);
                    }
                } else{
                    $reqData['reptileSerialCode'] = null;
                }
        
                $dbImgList = $cage->img_urls;
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
                    $imgUrls = $images->uploadImageForController($reqData['images'], 'cages');
                    $uploadImgList = array_merge($updateImgList, $imgUrls);
                    $uploadImgList = collect($uploadImgList)->flatten()->all(); // 2차원 배열을 1차원 배열로 변환
                } else{
                    $uploadImgList = $updateImgList;
                }

                $cage->update([
                    'name'                => $reqData['name'],
                    'reptile_serial_code' => $reqData['reptileSerialCode'],
                    'img_urls'            => $uploadImgList,
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
                if(!empty($cage->img_urls)){
                    // dd($cage->img_urls);
                    $images = new ImageController();
                    $deleteResult = $images->deleteImages($cage->img_urls);
                    if(gettype($deleteResult) !== 'boolean'){
                        return $deleteResult;
                    }
                }
    
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
    public function getTempHumData(Cage $cage)
    {
        $user = JWTAuth::user();

        try {
    
            if(empty($cage)){
                return response()->json([
                    'msg' => '사육장을 찾을 수 없음'
                ], 404);
            } else if($cage->user_id !== $user->id){
                return response()->json([
                    'msg' => '권한 없음'
                ], 403);
            } else if($cage->expired_at !== null){
                return response()->json([
                    'msg' => '만료된 데이터'
                ], 410);
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
                'setTemp' => ['required'],
                'setHum'    => ['required'],
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

    // 최신 온습도 데이터 전달(프론트에서 사용)
    public function getLatestTempHumData(Cage $cage)
    {
        $user = JWTAuth::user();

        try {

            if(empty($cage)){
                return response()->json([
                    'msg' => '사육장을 찾을 수 없음'
                ], 404);
            } else if($cage->user_id !== $user->id){
                return response()->json([
                    'msg' => '권한 없음'
                ], 403);
            } else if($cage->expired_at !== null){
                return response()->json([
                    'msg' => '만료된 데이터'
                ], 410);
            }

            $tempHumData = TemperatureHumidity::where('serial_code', $cage->serial_code)->latest()->first();

            $state = 200;

            $jsonData = ['msg' => '성공'];

            if(empty($tempHumData)){
                $jsonData['msg'] = '데이터 없음';
                $state = 204;
            } else{
                $jsonData['latestData'] = $tempHumData;
            }

            return response()->json($jsonData, $state);
            
        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 일별 시간당 평균 온습도 데이터 전달(프론트에서 사용)
    public function getDailyTempHumData(Cage $cage, String $date)
    {
        $user = JWTAuth::user();

        try {
            if(empty($cage)){
                return response()->json([
                    'msg' => '사육장을 찾을 수 없음'
                ], 404);
            } else if($cage->user_id !== $user->id){
                return response()->json([
                    'msg' => '권한 없음'
                ], 403);
            } else if($cage->expired_at !== null){
                return response()->json([
                    'msg' => '만료된 데이터'
                ], 410);
            }

            $tempHumData = TemperatureHumidity::selectRaw("
                                to_char(created_at, 'YYYY-MM-DD') as date,
                                EXTRACT(HOUR FROM created_at) as hour,
                                ROUND( CAST( AVG(temperature) as numeric ), 2 ) as avgTemp, 
                                ROUND( CAST( AVG(humidity) as numeric ), 2 ) as avgHum
                            ") // EXTRACT() : 날짜 및 시간에서 특정 필드 추출, CAST() : 데이터 타입 변환, postgresql에서는 CAST로 변환해야 함
                            ->where('serial_code', $cage->serial_code)
                            ->whereDate('created_at', $date)
                            ->groupBy('date', 'hour')
                            ->orderByRaw('date ASC, hour ASC')
                            ->get();

            $state = 200;
            $jsonData = ['msg' => '성공'];

            if($tempHumData->isEmpty()){
                $jsonData['msg'] = '데이터 없음';
                $state = 204;
            } else{
                $jsonData['avgData'] = $tempHumData;
            }

            return response()->json($jsonData, $state);
            
        } catch (Exception $e) {
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // 설정 온, 습도 하드웨어로 전달
    public function transmitTempHumData($serialCode, $setTemp, $setHum)
    {
        $mqtt = new MqttController();
        $result = $mqtt->sendData($serialCode, $setTemp, $setHum);
        return $result;
    }

    // 사육장 분양
    public function sellCage(Request $request)
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
                'category'  => 'cage_sales',
                'title'     => '케이지 분양 신청',
                'content'   => $user->nickname.' 유저가 케이지 분양을 신청하였습니다.',
                'readed'    => false,
                'sened_user_id' => $user->id,
                'img_urls'  => [],
                'created_at' => now()->toDateTimeString(),
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