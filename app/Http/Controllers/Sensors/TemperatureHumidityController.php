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
            'humidity'    => ['required', 'numeric', 'min:0', 'max:90'],
        ]);

        if($validator->fails()){
            Log::error('Validation failed', [
                'errors' => $validator->errors()->all(),
                'timestamp' => now()->toDateTimeString()
            ]);
        }

        $reqData = $validator->validated();

        try {
            $cageConfirm = Cage::where('serial_code', $reqData['serialCode'])->whereNull('expired_at')->first();

            $tempHigher = $reqData['temperature'] > $cageConfirm->set_temp; // 현재온도가 설정온도보다 높을 경우
            $tempLower = $reqData['temperature'] < $cageConfirm->set_temp; //   현재온도가 설정온도보다 낮을 경우

            $humidHigher = $reqData['humidity'] > $cageConfirm->set_humid; // 현재습도가 설정습도보다 높을 경우
            $humidLower = $reqData['humidity'] < $cageConfirm->set_humid; // 현재습도가 설정습도보다 낮을 경우



            // 현재온도가 설정 온도보다 높거나, 현재습도가 설정 습도보다 높을 경우 쿨러 동작 알림 발생
            if($tempHigher || $humidHigher){
                
                $title = $tempHigher ? '온도가 설정 온도보다 높습니다.' : '습도가 설정 습도보다 높습니다.';
                if($tempHigher && $humidHigher){
                    $title = '온도와 습도가 설정 온도와 습도보다 높습니다.';
                }

                Log::info('Temperature is higher than set temperature', [
                    'info' => $title,
                    'timestamp' => now()->toDateTimeString()
                ]);

                $content = '온도 : '.$reqData['temperature'].'℃ 습도 : '. $reqData['humidity'].'%, 쿨러를 작동합니다.';
                $this->operateModuleAlarm($cageConfirm, 'operating_cooler', $title, $content);
            }

            // 온도가 설정 온도보다 낮거나, 습도가 설정 습도보다 높을 경우 램프 동작 알림 발생
            if($tempLower || $humidHigher){
                $title = $tempLower ? '온도가 설정 온도보다 낮습니다.' : '습도가 설정 습도보다 높습니다.';
                if($tempLower && $humidHigher){
                    $title = '온도가 설정 온도보다 낮고, 습도가 설정 습도보다 높습니다.';
                }

                Log::info('Humidity is higher than set humidity', [
                    'info' => $title,
                    'timestamp' => now()->toDateTimeString()
                ]);

                $content = '온도 : '.$reqData['temperature'].'℃ 습도 : '. $reqData['humidity'].'%, 램프를 작동합니다.';
                $this->operateModuleAlarm($cageConfirm, 'operating_lamp', $title, $content);

            }

            // 온도가 설정 온도보다 높거나, 습도가 설정 습도보다 낮을 경우 가습기 동작 알림 발생
            if($tempHigher || $humidLower){
                $title = $tempHigher ? '온도가 설정 온도보다 높습니다.' : '습도가 설정 습도보다 낮습니다.';
                if($tempHigher && $humidLower){
                    $title = '온도가 설정 온도보다 높고, 습도가 설정 습도보다 낮습니다.';
                }

                Log::info('Humidity is lower than set humidity', [
                    'info' => $title,
                    'timestamp' => now()->toDateTimeString()
                ]);

                $content = '온도 : '.$reqData['temperature'].'℃ 습도 : '. $reqData['humidity'].'%, 가습기를 작동합니다.';
                $this->operateModuleAlarm($cageConfirm, 'operating_humidifier', $title, $content);
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

    // 모둘 동작 알림 발송
    public function operateModuleAlarm($cage, $category, $title, $content){

        $alarm = new AlarmController();

        $receiveData = [
            'user_id'   => $cage->user_id, // 받는 사람의 아이디
            'category'  => $category,
            'title'     => $title,
            'content'   => $content,
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
    

}
