<?php

namespace App\Http\Middleware;

use Closure;
use App\ApiLog;
use Symfony\Component\HttpFoundation\Response;

class ApiLogMiddleware
{
    public function handle($request, Closure $next)
    {
        $url = $request->url();
        $ip = $request->ip();
        $startTime = microtime(true);
        $response = $next($request);
        $time = microtime(true) - $startTime;

        $code = 0;
        if ($response instanceof Response) {
            $code = $response->getStatusCode();
        }

        ApiLog::create([
            'url' => $url,
            'time' => $time,
            'code' => $code,
            'ip' => $ip
        ]);

        return $response;
    }
}
