<?php

namespace App\Http\Controllers\Mqtt;

use App\Http\Controllers\Controller;
use PhpMqtt\Client\MqttClient;

class MqttController extends Controller
{

    public function sendData($serialCode, $temperature, $humidity){
        try {
            $server = config('mqtt-client.connections.default.host');
            $port = config('mqtt-client.connections.default.port');
            $clientId = 'cage-publisher-'.$serialCode;

            $mqtt = new MqttClient($server, $port, $clientId);
            $mqtt->connect();
            $mqtt->publish('cage/'.$serialCode, json_encode([ // 설명 : 토픽은 'cage/시리얼코드'로 하고, 메시지는 json 형태로 온도와 습도를 보낸다.
                'temperature' => $temperature,
                'humidity'    => $humidity,
            ]), 0); // QoS 0 : 메시지 전달 보장 안함, QoS 1 : 메시지 전달 보장함, 2 : 메시지 전달 보장함

            $mqtt->disconnect();

            return true;

        } catch (\Exception $e) {
            // 예외 처리 로직
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}