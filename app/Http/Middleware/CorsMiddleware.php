<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{

    protected $trustedOrigins = [
        'http://localhost:5174',
        'http://localhost:5173',
        'http://localhost:3000',
        'http://localhost:8080',
        'http://localhost:8000',
    ];

    /**
     * Handle an incoming request.
     * 
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $origin = $request->headers->get('Origin');

        if(in_array($origin, $this->trustedOrigins)){
            $response->headers->set('Access-Control-Allow-Origin', $origin);
            $response->headers->set('Access-Control-Allow-Methods', 'HEAD, GET, PUT, PATCH, POST, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Credentials', 'false');
        } else {
            $response->headers->set('Access-Control-Allow-Origin', '*');
            $response->headers->set('Access-Control-Allow-Methods', 'HEAD, GET, PUT, PATCH, POST, DELETE, OPTIONS');
            $response->headers->set('Access-Control-Allow-Credentials', 'false');
        }

        return $response;
    }
}
