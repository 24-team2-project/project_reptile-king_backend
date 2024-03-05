<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTAuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    // 로그인
    public function login(LoginUserRequest $request){
        $request->validated(); // 유효성 검사
        $request->safe();      // 잠재적 위험요소 제거
        $credentials = $request->only('email', 'password');

        try {
            if(!$access_token = JWTAuth::attempt($credentials) ){
                return response()->json([
                    'error' => 'Unauthorized : 로그인 정보 불일치', 401
                ]);
            }

            return $this->respondWithToken($access_token);

        } catch (Exception $e) {
            return response()->json([
                'login_error' => $e->getMessage()
            ]);
        }
        
    }

    // 로그아웃
    public function logout(){
        try {
        //    Auth::guard('api')->logout(); // 세션 기반 인증일 경우
            JWTAuth::invalidate(JWTAuth::getToken());

        } catch (Exception $e) {
            return response()->json([
                'logout_error' => $e->getMessage()
            ]);
        }

        return response()->json([
            'status' => 'success',
            'msg' => 'logout'
        ], 200);
    }

    // 토큰 재발급
    public function refresh(){
        return JWTAuth::refresh();
    }

    // 토큰 및 결과 전달
    protected function respondWithToken($access_token){
        return response()->json([
            'access_token' => $access_token,
            'token_type' => 'bearer',
            'msg' => '로그인 성공',
        ]);
    }


}
