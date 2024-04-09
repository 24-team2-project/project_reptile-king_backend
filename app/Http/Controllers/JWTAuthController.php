<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use Exception;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTAuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    // 로그인
    public function login(LoginUserRequest $request){

        try {
            $request->validated(); // 유효성 검사 

        } catch (ValidationException $e) {
            return response()->json([
                'msg'              => '유효성 검사 오류',
                'error' => $e->getMessage()
            ], 400);
        }
        
        $credentials = $request->safe()->only('email', 'password'); // 잠재적 위험요소 제거 및 방지(XSS) -> email, password만 추출

        try {
            if(!$accessToken = JWTAuth::attempt($credentials) ){
                return response()->json([
                    'msg' => '로그인 정보 불일치'
                ] , 401);
            }

            $response = response()->json([ 'msg' => '로그인 성공' ], 200);
            $response->headers->set('Authorization', 'Bearer '.$accessToken);
            return $response;

        } catch (Exception $e) {
            return response()->json([
                'msg'         => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }
        
    }

    // 로그아웃
    public function logout(){
        try {
        //    Auth::guard('api')->logout(); // 세션 기반 인증일 경우
            JWTAuth::invalidate(JWTAuth::getToken());

        } catch (Exception $e) {
            return response()->json([
                'msg'         => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'msg' => '로그아웃 성공'
        ], 200);
    }

    // 토큰 재발급
    public function refresh(){
        return JWTAuth::refresh();
    }

}
