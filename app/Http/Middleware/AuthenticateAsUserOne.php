<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthenticateAsUserOne
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Authenticate as the user with ID 1
        $user = User::find(1);

        if ($user) {
            Auth::login($user);  // Log in the user with ID 1
        }

        return $next($request);
    }
}
