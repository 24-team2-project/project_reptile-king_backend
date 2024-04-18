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
            $awsIp = '43.202.1.105';

            $mqtt = MQTT::connection();
            $mqtt->publish('cage/'.$serialCode, json_encode([
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