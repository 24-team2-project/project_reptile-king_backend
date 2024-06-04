<?php

namespace App\Http\Controllers\Sensors;

use App\Http\Controllers\Controller;
use App\Models\Cage;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SetLocationController extends Controller
{
    public function setLocation(Request $request){
        $data = $request->json()->all();
        $jsonToArray = json_decode($data['data'], true);

        $validator = Validator::make($jsonToArray, [
            'serialCode' => ['required', 'string'],
            'location'    => ['required', 'string']
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

            if(!empty($cageConfirm)){
                if($cageConfirm->location === null){
                    $cageConfirm->location = $reqData['location'];
                    $cageConfirm->save();
                }

            } else{
                Log::error('Serial code not found or already exists', [
                    'errors' => '일련번호를 찾을 수 없거나 이미 존재합니다.',
                    'timestamp' => now()->toDateTimeString()
                ]);
            }
        } catch (Exception $e) {
            Log::error('Server error', [
                'errors' => $e->getMessage(),
                'timestamp' => now()->toDateTimeString()
            ]);
        }
    }
}
