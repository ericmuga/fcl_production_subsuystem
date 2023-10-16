<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

        $data = DB::table('view_assets')
            ->selectRaw('COUNT(DISTINCT No_) as assets_count, COUNT(DISTINCT Responsible_employee) as users_count, COUNT(DISTINCT Location_code) as depts_count')
            ->first();

        $results = DB::table('asset_movements')
            ->selectRaw("COUNT(id) as total_count, SUM(CASE WHEN CAST(created_at AS DATE) = '" . today() . "' THEN 1 ELSE 0 END) as today_count")
            ->first();

        return view('assets.dashboard', compact('title', 'helpers', 'data', 'results'));
    }

    public function createMovement(Request $request, Helpers $helpers)
    {
        $title = "Create";

        $entries = DB::table('asset_movements')
            ->whereDate('asset_movements.created_at', today())
            ->join('users', 'asset_movements.user_id', '=', 'users.id')
            ->select('asset_movements.*', 'users.username')
            ->orderByDesc('id')
            ->get();

        // dd($entries);

        return view('assets.transactions', compact('title', 'helpers', 'entries'));
    }

    public function fetchData()
    {
        $data = Cache::remember('assets_list', now()->addMinutes(120), function () {
            return DB::table('view_assets')
                ->get();
        });

        return response()->json($data);
    }

    public function validateUserAssets(Request $request, Helpers $helpers)
    {
        $request_data = [
            "username" => $request->username,
            "password" => $request->password,
        ];

        $post_data = json_encode($request_data);

        $result = $helpers->validateLogin($post_data);

        return response()->json($result);
    }

    public function saveMovement(Request $request, Helpers $helpers)
    {
        $parts = explode(':', $request->fa);
        try {
            //insert 
            DB::table('asset_movements')->insert([
                'fa' => $parts[0],
                'description' => $parts[3],
                'to_dept' => $request->to_dept,
                'to_user' => $request->to_user,
                'from_dept' => $request->from_dept,
                'from_user' => $request->from_user,
                'user_id' => $helpers->authenticatedUserId(),
            ]);

            Toastr::success("Asset Movement entry to user : {$request->to_user} inserted successfully", 'Success');
            return redirect()
                ->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            Log::error('An exception occurred in ' . __FUNCTION__, ['exception' => $e]);
            return back();
        }
    }

    public function movementHistory()
    {
        $title = 'Movement History';

        $data = DB::table('asset_movements')
            ->join('users', 'asset_movements.user_id', '=', 'users.id')
            ->select('asset_movements.*', 'users.username')
            ->orderByDesc('id')
            ->get();

        return view('assets.history', compact('data', 'title'));
    }

    public function assetList()
    {
        $title = 'Asset List';

        $data = Cache::remember('assets_list', now()->addMinutes(120), function () {
            return DB::table('view_assets')
                ->get();
        });

        return view('assets.history', compact('data', 'title'));
    }

    public function getAssetEmployeeList()
    {
        $data = Cache::remember('asset_employees', now()->addMinutes(120), function () {
            return DB::table('view_employees')
                ->get();
        });

        return response()->json($data);
    }
}
