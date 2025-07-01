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

        $data = DB::connection('bc240')->table('FCL1$Fixed Asset$437dbf0e-84ff-417a-965d-ed2bb9650972')
            ->selectRaw('COUNT(DISTINCT No_) as assets_count, COUNT(DISTINCT [Responsible Employee]) as users_count, COUNT(DISTINCT [Location Code]) as depts_count')
            ->first();

        $results = DB::table('asset_movements')
            ->selectRaw("COUNT(id) as total_count, SUM(CASE WHEN CAST(created_at AS DATE) = '" . today() . "' THEN 1 ELSE 0 END) as today_count")
            ->first();

        return view('assets.dashboard', compact('title', 'helpers', 'data', 'results'));
    }

    public function createMovement(Request $request, Helpers $helpers)
    {
        $title = "Create";

        $departments = Cache::rememberForever('departments_data', function () {
            return DB::connection('bc240')
            ->table(DB::raw('[dbo].[FCL1$FA Location$437dbf0e-84ff-417a-965d-ed2bb9650972]'))
            ->select('Code', 'Name')
            ->where('Name', '!=', '')
            ->get()
            ->keyBy('Code'); // Index by 'Code' for faster lookup
        });

        $entries = DB::table('asset_movements')
            ->whereDate('asset_movements.created_at', today())
            ->join('users', 'asset_movements.user_id', '=', 'users.id')
            ->select('asset_movements.*', 'users.username')
            ->orderByDesc('asset_movements.id')
            ->get();

        $entries->transform(function ($entry) use ($departments) {
            $entry->department_name = $departments[$entry->to_dept]->Name ?? 'Unknown';
            return $entry;
        });    

        return view('assets.transactions', compact('title', 'helpers', 'entries'));
    }

    public function fetchData()
    {
        $data = Cache::rememberForever('fixed_assets_data', function () {
            return DB::connection('bc240')->table('FCL1$Fixed Asset$437dbf0e-84ff-417a-965d-ed2bb9650972 as a')
            ->where('a.FA Class Code', 'CE')
            ->select('a.No_', 'a.Description', 'a.Location Code', 'a.Responsible Employee')
            ->get();
        });

        return response()->json($data);
    }

    public function fetchDeptsData()
    {
        $data = Cache::rememberForever('depts_data', function () {
            return DB::connection('bc240')->table('FCL1$FA Location$437dbf0e-84ff-417a-965d-ed2bb9650972')
            ->select('Code', 'Name')
            ->where('Name', '!=', '')
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
        // dd($request->all());
        $parts = explode(':', $request->fa);
        try {
            //insert 
            DB::table('asset_movements')->insert([
                'fa' => $parts[0],
                'description' => $parts[1],
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
            return DB::connection('bc240')->table('FCL1$Fixed Asset$437dbf0e-84ff-417a-965d-ed2bb9650972 as a')
                ->where('a.FA Class Code', 'CE')
                ->select('a.No_', 'a.Description', 'a.Responsible Employee as Responsible_employee')
                ->orderBy('a.No_')
                ->get();
        });

        // dd($data);

        return view('assets.asset-list', compact('data', 'title'));
    }

    public function getAssetEmployeeList()
    {
        $data = DB::connection('bc240')->table('FCL1$Employee$437dbf0e-84ff-417a-965d-ed2bb9650972')
            ->SELECT('No_', 'First Name as FirstName', 'Last Name as lastName')
            ->orderBy('First Name')
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
