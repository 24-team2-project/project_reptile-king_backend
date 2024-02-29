<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTAuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login']]);
    }

    // 로그인
    public function login(LoginUserRequest $request){
        $validated = $request->validated();
        $validated = $request->safe();
        $credential = $request->only('email', 'password');

        try {
            if(! $token = JWTAuth::attempt($credential, env('JWT_TTL') )){
                return response()->json([
                    'error' => 'Unauthorized', 401
                ]);
            }
            
            return $this->respondWithToken($token);

        } catch (Exception $e) {
            return response()->json([
                'error' => '에러발생'.$e
            ]);
        }
        

    }

    // 로그아웃
    public function logout(Request $request){



    }

    // 토큰 및 결과 전달
    protected function respondWithToken($token){
        return response()->json([
            'access_token' => $token,
            'msg' => '로그인 성공',
        ]);
    }

    public function verificationToken(Request $request){
        
    }

}
