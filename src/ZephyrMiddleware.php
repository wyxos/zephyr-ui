<?php

namespace Wyxos\ZephyrUI;

use Closure;
use Exception;
use Illuminate\Http\Request;

class ZephyrMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!config('zephyr.token')) {
            throw new Exception('Zephyr is not configured. Please run `php artisan zephyr:setup`');
        }

        return $next($request);
    }
}
