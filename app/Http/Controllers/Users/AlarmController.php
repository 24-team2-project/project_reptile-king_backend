<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Models\Alarm;
use App\Models\Cage;
use App\Models\Reptile;
use App\Models\User;
use Exception;
use GuzzleHttp\Client;
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

    // 사용자 알림 리스트
    public function index()
    {
        $user = JWTAuth::user();

        try{
            $alarms = $user->alarms()->orderBy('created_at', 'desc')->get();

            return response()->json([
                'msg' => '알림 리스트 조회 성공',
                'alarms' => $alarms,
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // 알림 삭제
    public function destroy(Alarm $alarm)
    {
        try{
            $alarm->delete();

            return response()->json([
                'msg' => '알림 삭제 성공'
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // 알림 모두 확인
    public function checkAllAlarms(){
        $user = JWTAuth::user();

        try{
            $user->alarms()->update([
                'readed' => true
            ]);

            return response()->json([
                'msg' => '모든 알림 확인 성공'
            ], 200);

        }catch(Exception $e){
            return response()->json([
                'msg' => '서버 오류',
                'error' => $e->getMessage(),
            ], 500);
        }
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

            $expoTokens = [];   //  Expo Push Token
            $fcmTokens = [];    //  Firebase Cloud Messaging Token

            // Expo Push Token과 일반 FCM 토큰 구분
            foreach ($receiveUserTokens as $token) {
                if (strpos($token->token, 'ExponentPushToken') === 0) { // strpos() 함수는 문자열에서 특정 문자열이 처음으로 나타나는 위치를 찾습니다.
                    $expoTokens[] = $token->token;
                } else {
                    $fcmTokens[] = $token->token;
                }
            }

            $pushData = [
                'category' => $receiveData['category'],
            ];

            if(isset($receiveData['category_id'])){  // 카테고리 아이디가 있을 경우
                $pushData['category_id'] = $receiveData['category_id'];
            }

            // Expo Push Notification API 사용 (Expo Push Token인 경우)
            if (!empty($expoTokens)) {
                $client = new Client(); //  GuzzleHttp 클라이언트 생성
                foreach ($expoTokens as $expoToken) {
                    $response = $client->post('https://exp.host/--/api/v2/push/send', [ // GuzzleHttp 라이브러리 사용, post() 메서드는 POST 요청을 보냅니다.
                        'headers' => [  //  헤더 설정
                            'Accept' => 'application/json', //  Accept 헤더는 클라이언트가 이해할 수 있는 콘텐츠 유형을 서버에 알려줍니다.
                            'Content-Type' => 'application/json', //    Content-Type 헤더는 요청 본문의 미디어 유형을 서버에 알려줍니다.
                        ],
                        'json' => [ //  
                            'to' => $expoToken, // Expo Push Token
                            'sound' => 'default', // 기본 알림음
                            'title' => $receiveData['title'], //    제목
                            'body' => $receiveData['content'], //   내용
                            'data' => $pushData, // 추가 데이터
                        ],
                    ]);

                    if ($response->getStatusCode() !== 200) {
                        return response()->json([
                            'msg' => '알림 전송 실패',
                            'flag' => false,
                            'status' => 500,
                        ]);
                    }
                }
            }

            // Firebase Cloud Messaging 사용 (일반 FCM 토큰인 경우)
            if (!empty($fcmTokens)) {
                $messages = [];

                $receiveMessage = [
                    'title' => $receiveData['title'],
                    'body' => $receiveData['content'],
                ];


                foreach ($fcmTokens as $fcmToken) {
                    $messages[] = CloudMessage::withTarget('token', $fcmToken) // CloudMessage::withTarget() 메서드는 메시지를 보낼 대상을 설정합니다.
                        ->withNotification($receiveMessage) // withNotification() 메서드는 알림 메시지를 설정합니다.
                        ->withData($pushData) // withData() 메서드는 추가 데이터를 설정합니다.
                        ->withDefaultSounds(); // withDefaultSounds() 메서드는 기본 알림음을 설정합니다.
                }

                $resultCount = $this->messaging->sendAll($messages);

                if ($resultCount->successes() === 0) {
                    return response()->json([
                        'msg' => '알림 전송 실패',
                        'flag' => false,
                        'status' => 500,
                    ]);
                }
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
    
            if(empty($sendUserReptile) || $sendUserReptile->expired_at != null){
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

    // 케이지 분양 알림 수락
    public function acceptCageSale($alarm){
        $user = JWTAuth::user();

        try{
            $alarm->readed = true;
            $alarm->result = 'accept'; // 'accept' or 'reject
            $alarm->save();

            $sendUserCage = Cage::where('user_id', $alarm->send_user_id)->first();
    
            if(empty($sendUserCage) || $sendUserCage->expired_at != null){
                return response()->json([
                    'msg' => '분양자 케이지 없음',
                ], 204);
            }

            $sendUserCage->update([
                'expired_at' => now()->toDateTimeString(), // toDateTimeString() 메서드는 Carbon 인스턴스를 문자열로 변환합니다.
            ]);

            // 새 사용자의 파충류 등록
            Cage::create([
                'user_id' => $user->id,
                'serial_code' => $sendUserCage->serial_code,
                'species' => $sendUserCage->species,
                'name'     => $sendUserCage->name,
                'set_temp' => $sendUserCage->set_temp,
                'set_hum'  => $sendUserCage->set_hum,
                'img_urls' => $sendUserCage->img_urls,
            ]);


            $receiveData = [
                'user_id'   => $alarm->send_user_id, // 받는 사람의 아이디
                'category'  => 'cage_sales',
                'title'     => '사육장 분양 완료',
                'content'   => $user->nickname.' 유저에게 사육장 분양을 완료하였습니다.',
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

    // 케이지 분양 알림 거절
    public function rejectCageSale($alarm){
        $user = JWTAuth::user();

        try{
            $alarm->readed = true;
            $alarm->result = 'reject';
            $alarm->save();

            $receiveData = [
                'user_id'   => $alarm->send_user_id,
                'category'  => 'cage_sales',
                'title'     => '케이지 분양 거절',
                'content'   => $user->nickname.' 유저가 케이지 분양을 거절하였습니다.',
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
