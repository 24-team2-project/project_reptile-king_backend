<?php

namespace App\Http\Controllers\Mqtt;

use App\Http\Controllers\Controller;
use App\Models\Cage;
use PhpMqtt\Client\Facades\MQTT;
use PhpMqtt\Client\MqttClient;

class MqttController extends Controller
{

    public function sendData($serialCode, $temperature, $humidity){
        try {
            // $awsIp = '43.202.1.105';

            $mqtt = MQTT::connection();
            $mqtt->publish('cage/'.$serialCode, json_encode([ // 설명 : 토픽은 'cage/시리얼코드'로 하고, 메시지는 json 형태로 온도와 습도를 보낸다.
                'temperature' => $temperature,
                'humidity'    => $humidity,
            ]));
            $mqtt->disconnect();

            return true;

        } catch (\Exception $e) {
            // 예외 처리 로직
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}