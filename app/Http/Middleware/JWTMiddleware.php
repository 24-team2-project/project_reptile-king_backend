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
    public function handle(Request $request, Closure $next): Response {
        try {
            // 토큰 유효성 확인 및 인증
            JWTAuth::parseToken()->authenticate(); // 헤더에서 토큰을 파싱 후, 사용자 인증 후 저장 
        } catch (TokenInvalidException $e) {
            return response()->json(['msg' => '유효하지 않은 토큰', 'error' => $e->getMessage()], 401);
            // 401 : 요구되는 인증 정보를 누락하거나 잘못 지정해 요청한 경우, 지정한 리소스에 대한 액세스 권한이 없다.

        }catch (TokenExpiredException $e) {
            return response()->json(['msg' => '토큰 만료', 'error' => $e->getMessage()], 401);

        } catch(JWTException $e){
            return response()->json(['msg' => '토큰 오류', 'error' => $e->getMessage()], 401);
            
        } catch(Exception $e) {
            // 기타 오류
            return response()->json(['msg' => '서버 오류', 'error' => $e->getMessage()], 500);
        }
        // 토큰 유효성 검사 통과 시 다음 요청 진행
        return $next($request);
    }
}
