<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CheckUserSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user_id = Auth::id();

        if ($live_session = Session::get('live_session_id')) {
            $user = DB::table('users')->where('id', $user_id)->first();

            if ($live_session != $user->session) {
                // user session ended
                Session::put('session_message', 'Your Session has Expired. Please login to proceed');
                return redirect()->route('home');
            }
        } else {
            # no session exists
            Session::put('session_message', 'Session timed out! Please login to proceed');
            return redirect()->route('home');
        }

        return $next($request);
    }
}
