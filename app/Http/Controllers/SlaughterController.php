<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SlaughterController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function index()
    {
        $title = "dashboard";
        return view('slaughter.dashboard', compact('title'));
    }
}
