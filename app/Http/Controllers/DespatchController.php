<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DespatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check')->except(['']);
    }

    public function index()
    {
        $title = "dashboard";

        return view('despatch.dashboard', compact('title'));
    }
}
