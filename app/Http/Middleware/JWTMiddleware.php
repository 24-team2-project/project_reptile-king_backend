<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // 토큰 유효성 확인 및 인증
            JWTAuth::parseToken()->authenticate(); // 헤더에서 토큰을 파싱 후, 사용자 인증 

        } catch (Exception $e) {
            if ($e instanceof TokenInvalidException) {
                return response()->json(['status' => 'Token is Invalid : 토큰이 유효하지 않습니다'], 401);
                // 401 : 요구되는 인증 정보를 누락하거나 잘못 지정해 요청한 경우, 지정한 리소스에 대한 액세스 권한이 없다.

            } elseif ($e instanceof TokenExpiredException) {
                try {
                    // 만료된 토큰 갱신 시도
                    $refresh_token = JWTAuth::refresh();
                    JWTAuth::setToken($refresh_token);

                    // 갱신된 토큰으로 다음 요청 진행
                    $response = $next($request);

                    // 응답에 갱신된 토큰 포함
                    $content = json_decode($response->getContent(), true);
                    if (json_last_error() === JSON_ERROR_NONE) { // json_decode()가 에러가 아니면(null이 아니면)
                        $content['refresh_token'] = $refresh_token;
                        $content = json_encode($content);
                        $response->setContent($content);
                    }

                    return $response;

                } catch (JWTException $e) {
                    // 갱신 실패 
                    return response()->json([
                        'code' => 103,
                        'msg' => 'Token cannot be refreshed, Try login again : 토큰을 새로 고칠 수 없습니다. 다시 로그인해 보세요.',
                        'exception' => $e->getMessage(),
                    ], 401);
                }
                
            } else {
                // 인증 토큰 없음
                return response()->json(['status' => 'Authorization Token not found : 인증 토큰을 찾을 수 없습니다'], 401);
            }
        }

        // 토큰 유효성 검사 통과 시 다음 요청 진행
        return $next($request);
    }
}
