<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HighCare2Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check')->except([]);
    }

    public function index()
    {
        return "<h1>This Section is still in development Mode</h1>";
    }
}
