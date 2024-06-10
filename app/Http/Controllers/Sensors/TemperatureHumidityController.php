<?php

namespace App\Http\Controllers\Sensors;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Users\AlarmController;
use App\Models\Cage;
use App\Models\TemperatureHumidity;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class TemperatureHumidityController extends Controller
{
    // 온습도 데이터 저장
    public function store(Request $request)
    {
        $data = $request->json()->all();
        $jsonToArray = json_decode($data['data'], true);

        $validator = Validator::make($jsonToArray, [
            'serialCode' => ['required', 'string'],
            'temperature' => ['required', 'numeric', 'min:10', 'max:40'],
            'humidity'    => ['required', 'integer', 'min:0', 'max:90'],
        ]);

        if($validator->fails()){
            Log::error('Validation failed', [
                'errors' => $validator->errors()->all(),
                'timestamp' => now()->toDateTimeString()
            ]);
        }

        $reqData = $validator->validated();

        try {
            $cageConfirm = Cage::where([
                ['serial_code', $reqData['serialCode']],
                ['expired_at', null] 
            ])->first();

            if($reqData['temperature'] > $cageConfirm->set_temp){
                Log::info('Temperature is higher than set temperature', [
                    'info' => '온도가 설정 온도보다 높습니다.',
                    'timestamp' => now()->toDateTimeString()
                ]);

                // 온도가 설정 온도보다 높을 경우 알람 발생
                $alarm = new AlarmController();
                $receiveData = [
                    'user_id'   => $cageConfirm->user_id, // 받는 사람의 아이디
                    'category'  => 'temp_abnormality',
                    'title'     => '온도가 설정 온도보다 높습니다.',
                    'content'   => '온도: '.$reqData['temperature'].'℃, 쿨러를 작동합니다.',
                    'readed'    => false,
                    'img_urls'  => [],
                    'created_at' => now()->toDateTimeString(),
                ];  

                $result = $alarm->sendAlarm($receiveData);

                if($result['flag']){
                    Log::info('Alarm sent successfully', [
                        'info' => '알람이 성공적으로 전송되었습니다.',
                        'timestamp' => now()->toDateTimeString()
                    ]);
                } else{
                    Log::error('Failed to send alarm', [
                        'errors' => '알람 전송에 실패했습니다.',
                        'timestamp' => now()->toDateTimeString()
                    ]);
                }
            }

            // 습도가 설정 습도보다 낮을 경우 알람 발생
            if($reqData['humidity'] < $cageConfirm->set_humid){
                Log::info('Humidity is higher than set humidity', [
                    'info' => '습도가 설정 습도보다 낮습니다.',
                    'timestamp' => now()->toDateTimeString()
                ]);

                $alarm = new AlarmController();
                $receiveData = [
                    'user_id'   => $cageConfirm->user_id, // 받는 사람의 아이디
                    'category'  => 'humid_abnormality',
                    'title'     => '습도가 설정 습도보다 낮습니다.',
                    'content'   => '습도: '.$reqData['humidity'].'%, 가습기를 작동합니다.',
                    'readed'    => false,
                    'img_urls'  => [],
                    'created_at' => now()->toDateTimeString(),
                ];  

                $result = $alarm->sendAlarm($receiveData);

                if($result['flag']){
                    Log::info('Alarm sent successfully', [
                        'info' => '알람이 성공적으로 전송되었습니다.',
                        'timestamp' => now()->toDateTimeString()
                    ]);
                } else{
                    Log::error('Failed to send alarm', [
                        'errors' => '알람 전송에 실패했습니다.',
                        'timestamp' => now()->toDateTimeString()
                    ]);
                }
            }

            if(!empty($cageConfirm)){
                TemperatureHumidity::create([
                    'serial_code' => $reqData['serialCode'],
                    'temperature' => $reqData['temperature'],
                    'humidity'    => $reqData['humidity'],
                    'created_at'  => now()->toDateTimeString()
                ]);
            } else{
                Log::error('Serial code not found or already exists', [
                    'errors' => '일련번호를 찾을 수 없거나 이미 존재합니다.',
                    'timestamp' => now()->toDateTimeString()
                ]);
            }

        } catch (Exception $e) {
            Log::error('Excption Error', [
                'errors' => $e->getMessage(),
                'timestamp' => now()->toDateTimeString()
            ]);
        }

    }

    

}
