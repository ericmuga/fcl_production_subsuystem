<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AssetController extends Controller
{
    protected $layout = 'assets';

    public function __construct()
    {
        $this->middleware('auth');
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
            ->join('view_depts', 'asset_movements.to_dept', '=', DB::raw('view_depts.Code collate Latvian_BIN'))
            ->select('asset_movements.*', 'users.username')
            ->orderByDesc('id')
            ->get();

        return view('assets.transactions', compact('title', 'helpers', 'entries'));
    }

    public function fetchData()
    {
        $data = Cache::remember('assets_list', now()->addMinutes(120), function () {
            return DB::table('view_assets as a')
                ->where('a.FA Class Code', 'CE')
                ->get();
        });

        return response()->json($data);
    }

    public function fetchDeptsData()
    {
        $data = DB::table('view_depts')
            ->select('Code', 'Name')
            ->get();

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
                'authenticated_username' => $request->auth_username,
                'user_id' => Auth::id(),
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

        $data = DB::table('asset_movements as a')
            ->join('users', 'a.user_id', '=', 'users.id')
            ->where('a.status', 1)
            ->select('a.*', 'users.username')
            ->orderByDesc('id')
            ->get();

        return view('assets.history', compact('data', 'title'));
    }

    public function assetList()
    {
        $title = 'Asset List';

        $data = Cache::remember('assets_list', now()->addMinutes(120), function () {
            return DB::table('view_assets as a')
                ->where('a.FA Class Code', 'CE')
                ->get();
        });

        return view('assets.asset-list', compact('data', 'title'));
    }

    public function getAssetEmployeeList()
    {
        $data = DB::table('view_employees')
            ->get();

        return response()->json($data);
    }

    public function cancelMovement(Request $request, Helpers $helpers)
    {
        // dd($request->all());
        try {
            // update
            DB::table('asset_movements')
                ->where('id', $request->item_id)
                ->update([
                    'status' => 2, //cancelled
                    'updated_at' => now(),
                ]);

            Toastr::success("movement entry for {$request->edit_desc} cancelled successfully", 'Success');
            return redirect()->back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back()
                ->withInput();
        }
    }
}
