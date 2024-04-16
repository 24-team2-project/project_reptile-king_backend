<?php

namespace App\Http\Controllers\Sensors;

use App\Http\Controllers\Controller;
use App\Models\Cage;
use App\Models\CageSerialCode;
use App\Models\TemperatureHumidity;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class TemperatureHumidityController extends Controller
{
    // 온습도 데이터 저장
    public function store(Request $request)
    {
        $data = $request->json()->all();
        $jsonToArray = json_decode($data['data'], true);

        $validator = Validator::make($jsonToArray, [
            'serialCode' => ['required', 'string'],
            'temperature' => ['required', 'numeric'],
            'humidity'    => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        if($validator->fails()){
            Log::error('Validation failed', [
                'errors' => $validator->errors()->all(),
                'timestamp' => now()->toDateTimeString()
            ]);
        }

        $reqData = $validator->validated();

        try {
            $cageConfirm = Cage::where('serial_code', $reqData['serialCode']);

            if(!empty($cageConfirm)){
                TemperatureHumidity::create([
                    'serial_code' => $reqData['serialCode'],
                    'temperature' => $reqData['temperature'],
                    'humidity'    => $reqData['humidity'],
                    'created_at'  => now()
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
