<?php

namespace App\Http\Middleware;

use App\Models\ApiKey;
use App\Traits\ApiReturnFormatTrait;
use Closure;
use Illuminate\Http\Request;

class CheckApiKeyMiddleware
{
    use ApiReturnFormatTrait;

    public function handle(Request $request, Closure $next)
    {
        if ($request->hasHeader('apiKey')) {
            $key = ApiKey::where('key', $request->header('apiKey'))->first();
            if ($key) {
                return $next($request);
            }
        }

        return $next($request);
    }
}
