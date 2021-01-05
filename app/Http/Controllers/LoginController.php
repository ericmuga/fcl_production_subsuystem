<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class LoginController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth')->except(['getLogin', 'processLogin']);
    }

    public function getLogin()
    {
        $computer_name = gethostname();
        return view('auth.login', compact('computer_name'));

    }

    public function processLogin(Request $request)
    {
        $credentials = $request->only('email', 'password');
        dd($credentials);
        if (Auth::attempt($credentials)) {

            return redirect()->intended('slaughter_dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);

    }

    public function getLogout(Request $request)
    {
        Session::flush();
        Auth::logout();
        dd('here');
        return redirect()->route('login_page');
    }
}
