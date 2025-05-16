<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

final class ForceJson
{
    public function __invoke(Request $request, Closure $next): mixed
    {
        if (str_starts_with($request->path(), 'api')) {
            $request->headers->set('Accept', 'application/json');
        }

        return $next($request);
    }
}
