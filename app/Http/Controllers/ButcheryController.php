<?php

namespace App\Http\Controllers;
use Brian2694\Toastr\Facades\Toastr;

use Illuminate\Http\Request;

class ButcheryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $title = "dashboard";
        return view('butchery.dashboard', compact('title'));
    }

    public function scaleOne()
    {
        $title = "Scale-1";
        return view('butchery.scale1', compact('title'));
    }

    public function scaleTwo()
    {
        $title = "Scale-2";
        return view('butchery.scale2', compact('title'));
    }

    public function scaleThree()
    {
        $title = "Scale-3";
        return view('butchery.scale3', compact('title'));
    }

    public function products()
    {
        $title = "products";
        return view('butchery.products', compact('title'));
    }

    public function scaleSettings()
    {
        $title = "Scale";
        return view('butchery.scale_settings', compact('title'));
    }

    public function changePassword()
    {
        $title = "password";
        return view('butchery.change_password', compact('title'));
    }
}
