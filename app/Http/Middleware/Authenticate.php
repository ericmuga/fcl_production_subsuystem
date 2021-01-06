<?php

namespace App\Http\Middleware;
use Brian2694\Toastr\Facades\Toastr;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

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
        if (! $request->expectsJson()) {
            Toastr::warning('Your Session has expired. Please login again','Success');
            return route('login');
        }
    }
}
