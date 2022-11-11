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
        return "This Section is still in development Mode";
    }
}
