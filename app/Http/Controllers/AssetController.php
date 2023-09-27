<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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

        return view('assets.dashboard', compact('title', 'helpers'));
    }

    public function createMovement(Request $request, Helpers $helpers)
    {
        $title = "Create";

        $data = Cache::remember('assets_list', now()->addMinutes(120), function () {
            return DB::table('view_assets')
                ->take(100)
                ->get();
        });

        // dd($data);

        return view('assets.transactions', compact('title', 'helpers', 'data'));
    }

    public function fetchData()
    {
        $data = Cache::remember('assets_list', now()->addMinutes(120), function () {
            return DB::table('view_assets')
                ->get();
        });

        return response()->json($data);
    }

    public function validateUser(Request $request, Helpers $helpers)
    {
        $request_data = [
            "username" => $request->username,
            "password" => $request->password,
        ];

        $post_data = json_encode($request_data);

        $result = $helpers->validateLogin($post_data);

        return response()->json($result);
    }
}
