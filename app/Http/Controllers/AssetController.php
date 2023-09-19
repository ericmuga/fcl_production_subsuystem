<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    protected $layout = 'assets';

    public function __construct()
    {
        $this->middleware('session_check');
        view()->share('layout', $this->layout);
    }

    public function index(Helpers $helpers)
    {
        $title = "dashboard";

        // $layout = 'assets';

        return view('assets.dashboard', compact('title', 'helpers'));
    }

    public function createMovement(Request $request)
    {
        $title = "dashboard";

        // $layout = 'assets';

        return view('assets.transactions', compact('title', 'helpers'));
    }
}
