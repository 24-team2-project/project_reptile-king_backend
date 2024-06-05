<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Alarm;
use App\Models\Reptile;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Laravel\Firebase\Facades\Firebase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AlarmController extends Controller
{
    public $messaging;

    public function __construct()
    {
        $this->messaging = Firebase::messaging();
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store()
    {

        
    }

    /**
     * Display the specified resource.
     */
    public function show(Alarm $alarm)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Alarm $alarm)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Alarm $alarm)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Alarm $alarm)
    {
        //
    }

    // 알림 확인
    public function checkAlarm(Alarm $alarm){
        try{
            $alarm->readed = true;
            $alarm->save();

            return response()->json([
                'msg' => '알림 확인 성공'
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // 알림 보내기
    public function sendAlarm($receiveData){
        try{
            Alarm::create($receiveData);

            $receiveUser = User::where('id', $receiveData['user_id'])->first();
            $receiveUserTokens = $receiveUser->fcmTokens; // 받는 사람의 토큰

            if($receiveUserTokens->isEmpty()){
                $result = [
                    'msg' => '토큰 없음',
                    'flag' => false,
                    'status' => 204,
                ];

                return $result;
            }

            $receiveTokens = $receiveUserTokens->pluck('token')->toArray();

            $messages = [];

            $receiveMessage = [
                    'title' => $receiveData['title'],
                    'body' => $receiveData['content'],
            ];

            foreach ($receiveTokens as $token) {
                $messages[] = CloudMessage::withTarget('token', $token)
                    ->withNotification($receiveMessage)
                    ->withDefaultSounds();
            }

            $resultCount = $this->messaging->sendAll($messages);

            if($resultCount->successes() === 0){
                $result = [
                    'msg' => '알림 전송 실패',
                    'flag' => false,
                    'status' => 500,
                ];
                return $result;
            }

            $result = [
                'msg' => '알림 전송 성공',
                'flag' => true,
                'status' => 200,
            ];
            
            return $result;
        
        }catch(Exception $e){
            $result = [
                'msg' => '서버 오류'.$e->getMessage(),
                'flag' => false,
                'status' => 500,
            ];
            return $result;
        }
    }

    // 파충류 분양 알림 수락
    public function acceptReptileSale(Alarm $alarm){
        $user = JWTAuth::user();

        try{
            $alarm->readed = true;
            $alarm->result = 'accept'; // 'accept' or 'reject
            $alarm->save();

            $sendUserReptile = Reptile::where('user_id', $alarm->send_user_id)->first();
    
            if(empty($sendUserReptile)){
                return response()->json([
                    'msg' => '분양자 파충류 없음',
                ], 204);
            }

            $sendUserReptile->update([
                'expired_at' => now()->toDateTimeString(), // toDateTimeString() 메서드는 Carbon 인스턴스를 문자열로 변환합니다.
            ]);

            // 새 사용자의 파충류 등록
            Reptile::create([
                'user_id' => $user->id,
                'serial_code' => $sendUserReptile->serial_code,
                'species' => $sendUserReptile->species,
                'gender'   => $sendUserReptile->gender,
                'birth'    => $sendUserReptile->birth,
                'name'     => $sendUserReptile->name,
                'img_urls' => $sendUserReptile->img_urls,
            ]);

            $receiveData = [
                'user_id'   => $alarm->send_user_id, // 받는 사람의 아이디
                'category'  => 'reptile_sales',
                'title'     => '파충류 분양 완료',
                'content'   => $user->nickname.' 유저에게 파충류 분양을 완료하였습니다.',
                'readed'    => false,
                'img_urls'  => [],
                'created_at' => now()->toDateTimeString(),
            ];

            $result = $this->sendAlarm($receiveData);

            return response()->json([
                'msg' => $result['msg']
            ], $result['status']);


        }catch(Exception $e){
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    //파충류 분양 알림 거절
    public function rejectReptileSale(Alarm $alarm){
        $user = JWTAuth::user();

        try{
            $alarm->readed = true;
            $alarm->result = 'reject';
            $alarm->save();

            $receiveData = [
                'user_id'   => $alarm->send_user_id,
                'category'  => 'reptile_sales',
                'title'     => '파충류 분양 거절',
                'content'   => $user->nickname.' 유저가 파충류 분양을 거절하였습니다.',
                'readed'    => false,
                'img_urls'  => [],
                'created_at' => now()->toDateTimeString(),
            ];

            $result = $this->sendAlarm($receiveData);

            return response()->json([
                'msg' => $result['msg']
            ], $result['status']);

        }catch(Exception $e){
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    



}
