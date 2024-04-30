<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;
use Tymon\JWTAuth\Contracts\Providers\JWT;
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
            $user = JWTAuth::user();
            
            // $ttl = 60 *60 * 24 * 14; // 14일
            $ttl = 60 *60 * 24 * 7; // 7일

            $refreshToken = auth()->setTTL($ttl)->attempt($credentials); 

            // redis 저장
            Redis::setex('refresh_token_'.$user->id, $ttl, $refreshToken);

            $response = response()->json([ 'msg' => '로그인 성공' ], 200);
            $response->headers->set('Authorization', 'Bearer '.$accessToken);
            $response->headers->set('Refresh-Token', 'Bearer '.$refreshToken);
            return $response;

        } catch (Exception $e) {
            return response()->json([
                'msg'         => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }
        
    }

    // 로그아웃
    public function logout(Request $request){
        try {
        //    Auth::guard('api')->logout(); // 세션 기반 인증일 경우
            $accessToken = JWTAuth::getToken();
            $refreshToken = $request->header('Refresh-Token');
            if(strpos($refreshToken, 'Bearer') !== false){
                $refreshToken = str_replace('Bearer ', '', $refreshToken);
            }

            $user = JWTAuth::user();
            Redis::del('refresh_token_'.$user->id);
            
            JWTAuth::invalidate($accessToken);
            JWTAuth::invalidate($refreshToken);

            return response()->json([
                'msg' => '로그아웃 성공'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'msg'         => '서버 오류',
                'error' => $e->getMessage()
            ], 500);
        }

        
    }

    // 토큰 재발급
    public function refresh(){
        $user = JWTAuth::user();
        
        $redisData = Redis::get('refresh_token_'.$user->id);
        $token = JWTAuth::getToken()->get();
        if($redisData !== $token){
            JWTAuth::invalidate($redisData);
            Redis::del('refresh_token_'.$user->id);
            return response()->json([
                'msg' => '토큰 갱신 실패 : 불일치, 재로그인 필요',
                'reqToken' => $token,
                'redisData' => $redisData,
            ], 401);
        }

        $newToken = auth()->login($user);

        $response = response()->json([ 'msg' => '토큰 갱신 성공' ], 200);
        $response->headers->set('Authorization', 'Bearer '.$newToken);

        return $response;
    }

}
