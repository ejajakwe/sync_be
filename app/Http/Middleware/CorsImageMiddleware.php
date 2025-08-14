<?php

namespace App\Http\Middleware;

use Closure;

class CorsImageMiddleware
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:5173');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        return $response;
    }
}
