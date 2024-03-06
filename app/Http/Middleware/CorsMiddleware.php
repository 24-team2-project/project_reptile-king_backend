<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        header('Access-Control-Allow-Origin: *'); // 어떤 url을 허용할 것인지에 대한 헤더
        header('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS'); // 어떤 요청 메서드를 허용할 것인지
        header('Access-Control-Allow-Credentials: false'); //인증정보를 포함한 요청(쿠키), 쿠키를 사용하지 않는다면 false, 사용하면 true
        return $next($request);
    }
}
