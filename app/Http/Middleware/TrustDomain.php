<?php

namespace App\Http\Middleware;

use Closure;

class TrustDomain
{
    public function handle($request, Closure $next)
    {
        // Check if the request originated from the allowed domain
        if (
            !str_contains($request->headers->get('referer'), 'dispatching.mytripline.com')
            &&
            !str_contains($request->headers->get('referer'), 'localhost')
        ) {
            return response('Unauthorized.', 401);
        }

        return $next($request);
    }
}
