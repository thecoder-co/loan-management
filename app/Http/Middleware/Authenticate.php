<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        return null;
    }
    // public function handle($request, \Closure $next, ...$guards): JsonResponse
    // {
    //     if ($this->authenticate($request, $guards) === null) {
    //         return response()->json(['error' => 'Unauthorized'], 401);
    //     }

    //     return $next($request);
    // }
}
