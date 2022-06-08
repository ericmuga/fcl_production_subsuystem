<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SpicesController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check');
    }

    public function index()
    {
        $title = "dashboard";

        return view('spices.dashboard', compact('title'));
    }
}
