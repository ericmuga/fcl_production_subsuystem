<?php

namespace App\Http\Controllers;

use App\Models\Helpers;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth', ['only' => ['getSectionRedirect', 'getLogout', 'users', 'updateUser', 'getUserPermissionsAxios']]);
    }

    public function home()
    {
        if (Auth::check()) {
            $title = "Navigation";

            $user_permissions = DB::table('user_permissions')
                ->join('permissions', 'user_permissions.permission_code', '=', 'permissions.code')
                ->where('user_permissions.user_id', Session::get('session_userId'))
                ->select('permissions.*')
                ->get();

            return view('layouts.router', compact('title', 'user_permissions'));
           
        } else {
            // If user is not logged in render the login page
            $title = 'Login';
            return view('auth.login', compact('title'));
        }
    }

    public function processLogin(Request $request, Helpers $helpers)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);


        if ($validator->fails()) {
            # failed validation
            $messages = $validator->errors();
            foreach ($messages->all() as $message) {
                Toastr::error($message, 'Error!');
            }
            return back();
        }

        // attempt api login
        $domain_user = "FARMERSCHOICE\\" . $request->username;

        $request_data = [
            "username" => $domain_user,
            "password" => $request->password,
        ];

        $post_data = json_encode($request_data);

        $result = $helpers->validateLogin($post_data);
        $res = json_decode($result, true);

        if ($res == null) {
            # no response from api service
            Toastr::error('No response from login Api service. Contact IT', 'Error!');
            return back();
        }

        if ($res['success'] != true) {
            # failed login
            Toastr::warning('Wrong username or password. Please try again', 'Warning!');
            return back();
        }

        // auth check successful, check if user exists, else create
        $user = User::firstOrCreate(
            ['username' => $request->username],
            ['email' => strtolower($request->username) . "@farmerschoice.co.ke", 'role' => 'user']
        );

        // Log in the user
        Auth::login($user);

        // regenerate session to prevent session fixation
        $request->session()->regenerate();

        //Log username into session
        Session::put('session_username', $request->username);
        Session::put('session_userRole', $user->role);

        # Redirecting
        Toastr::success('Successful login', 'Success');
        return redirect()->route('home');
    }

    public function getLogout(Request $request)
    {
        Auth::logout();
    
        $request->session()->invalidate();
    
        $request->session()->regenerateToken();
    
        Toastr::success('Logout successful', 'Success');
        return redirect()->route('home');
    }

    public function users(Helpers $helpers)
    {
        $title = "Users";

        $users = DB::table('users')->get();

        $permissions = Cache::remember('permissions_list', now()->addHours(10), function () {
            return DB::table('permissions')
                ->select('code', 'permission')
                ->get();
        });

        return view('users.users', compact('title', 'users', 'helpers', 'permissions'));
    }

    public function getUserPermissionsAxios(Request $request)
    {
        $permissions = DB::table('user_permissions')
            ->select('permission_code')
            ->where('user_id', $request->user_id)
            ->get()->toArray();

        return response()->json($permissions);
    }

    public function updateUser(Request $request)
    {
        // dd($request->all());
        try {
            //update
            DB::transaction(function () use ($request) {
                // delete existing permissions of same user
                DB::table('user_permissions')
                    ->where('id', $request->user_id)
                    ->delete();
                
                foreach ($request->permission_code as $code) {
                    DB::table('user_permissions')->insert(
                        [
                            'user_id' => $request->user_id,
                            'permission_code' => $code
                        ]);
                }
            });
            Toastr::success('User permissions for '.$request->editname.' updated successfully', 'Success');
            return back();
        } catch (\Exception $e) {
            Toastr::error($e->getMessage(), 'Error!');
            return back();
        }
    }
}
