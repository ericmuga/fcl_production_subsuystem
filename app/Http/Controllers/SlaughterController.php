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

    public function weigh()
    {
        $title = "weigh";
        return view('slaughter.weigh', compact('title'));
    }

    public function import()
    {
        $title = "import";
        return view('slaughter.import', compact('title'));

    }

    public function importedReceipts()
    {
        $title = "receipts";
        return view('slaughter.receipts', compact('title'));

    }

    public function slaughterDataReport()
    {
        $title = "receipts";
        return view('slaughter.receipts', compact('title'));

    }

    public function scaleSettings()
    {
        $title = "scale";
        return view('slaughter.scale_settings', compact('title'));
    }

    public function changePassword()
    {
        $title = "password";
        return view('slaughter.change_password', compact('title'));
    }
}
