<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function login()
    {
        $computer_name = gethostname();
        return view('auth.login', compact('computer_name'));

    }
}
