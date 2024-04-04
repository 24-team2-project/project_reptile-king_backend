<?php

namespace App\Http\Controllers\Reptiles;

use App\Http\Controllers\Controller;
use App\Models\Cage;
use App\Models\CageSerialCode;
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

            return response()->json([
                'msg'   => '성공',
                'cages' => $cages->isEmpty() ? '데이터 없음' : $cages,
            ], 200);

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
        $validator = Validator::make($request->all(), [
            'reptile_id' => ['nullable'],
            'memo'       => ['nullable', 'string'],
            'set_temp'   => ['required'],
            'set_hum'   => ['required'],
            'serial_code'   => ['required', 'string'],
        ]);

        if($validator->fails()){
            return response()->json([
                'msg'   => '유효성 검사 오류',
                'error' => $validator->errors()->all(),
            ], 400);
        }
        
        $reqData = $validator->safe();

        try {
            $msg = '';
            $state = 201;

            // 파충류 등록 유무 확인
            if($reqData['reptile_id'] !== null){
                $cageConfirm = Cage::where('reptile_id', $reqData['reptile_id'])->first();
                if(!empty($cageConfirm)){ 
                    $msg = '이미 등록된 파충류';
                    $state = 400;
                }
            }

            $serialCodeConfirm = CageSerialCode::where('serial_code', $reqData['serial_code'])->first();
            // 일련번호 확인
            if(empty($serialCodeConfirm)){
                $msg = '일련번호를 찾을 수 없음';
                $state = 400;

            } else{
                $user = JWTAuth::user();

                Cage::create([
                    'user_id'       => $user->id,
                    'reptile_id'    => $reqData['reptile_id'],
                    'memo'          => $reqData['memo'],
                    'set_temp'      => $reqData['set_temp'],
                    'set_hum'       => $reqData['set_hum'],
                    'serial_code'   => $reqData['serial_code']
                ]);

                $msg = '등록 완료';
            }

            return response()->json([
                'msg' => $msg,
            ], $state);

        } catch (Exception $e) {
            return response()->json([
                'msg'   => '서버 오류',
                'error' => $e->getMessage()
            ]);
        }

    }

    // 사육장 정보
    public function show(Cage $cage)
    {
        $user = JWTAuth::user();

        if($cage->user_id !== $user->id){
            return response()->json([
                'msg' => '권한 없음'
            ], 403);
        } 

        return response()->json([
            'msg' => '성공',
            'reptile' => $cage
        ], 200);
    }

    // 사육장 정보 수정
    public function update(Request $request, Cage $cage)
    {
        $user = JWTAuth::user();

        if($cage->user_id !== $user->id){
            return response()->json([
                'msg' => '권한 없음'
            ], 403);
        }

        try {
            $cage->update($request->all());

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

    // 사육장 정보 삭제
    public function destroy(Cage $cage)
    {
        $user = JWTAuth::user();

        if($cage->user_id !== $user->id){
            return response()->json([
                'msg' => '권한 없음'
            ], 403);
        }

        try {
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
