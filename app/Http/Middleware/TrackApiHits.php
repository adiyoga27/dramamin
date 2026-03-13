<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackApiHits
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->user() && ($token = $request->user()->currentAccessToken())) {
            /** @var \Laravel\Sanctum\PersonalAccessToken $token */
            $token->increment('hits');
        }

        return $response;
    }
}
