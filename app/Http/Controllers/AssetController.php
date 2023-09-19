<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function __construct()
    {
        $this->middleware('session_check');
    }

    public function index(Helpers $helpers)
    {
        $title = "dashboard";

        $layout = 'assets';

        return view('assets.dashboard', compact('title', 'layout', 'helpers'));
    }
}
