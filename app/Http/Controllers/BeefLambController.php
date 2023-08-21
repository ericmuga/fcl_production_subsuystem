<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Illuminate\Http\Request;

class BeefLambController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check');
    }

    public function index(Helpers $helpers)
    {
        $title = "dashboard";

        $layout = 'beef';


        return view('beef_lamb.dashboard', compact('title', 'layout', 'helpers'));
    }
}
