<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckBearerToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */

    public function handle(Request $request, Closure $next)
    {
        $header = $request->header('Authorization');

        $validToken = 'Bearer ' . config('app.valid_bearer_token');

        if (empty($header)) {
            return response()->json(['success' => false, 'message' => 'Header Authorization is missing in request'], 401);
        }

        if ($header !== $validToken) {
            return response()->json(['success' => false, 'message' => 'Invalid Header Authorization was supplied'], 401);
        }

        return $next($request);
    }
}
