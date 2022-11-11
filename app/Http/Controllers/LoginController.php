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
        $this->middleware('session_check', ['only' => ['getSectionRedirect', 'getLogout', 'users', 'updateUser']]);
    }

    public function getLogin()
    {
        return view('auth.login');
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

        # login successful
        if (!DB::table('users')->where('username', '=', $request->username)->exists()) {
            # user not in db, add user
            $new_user = $helpers->addUser($request->username);

            if ($new_user != 1) {
                # failed add user to db
                Toastr::error($new_user, 'Error!');
                return back();
            }
        }

        $user = DB::table('users')->where('username', $request->username)->first();

        # Check if session exists and log out the previous session
        $previous_session = $user->session;

        if ($previous_session) {
            \Session::getHandler()->destroy($previous_session);
        }

        Session::put('session_userId', $user->id);
        Session::put('session_userName', $user->username);
        Session::put('session_role', $user->role);
        Session::put('live_session_id', sha1(microtime()));

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'session' => Session::get('live_session_id'),
                'updated_at' => now(),
            ]);

        # Redirecting
        Toastr::success('Successful login', 'Success');
        return $this->getSectionRedirect();
    }

    public function getSectionRedirect()
    {
        $title = "Redirecting";

        $user_permissions = DB::table('user_permissions')
            ->join('permissions', 'user_permissions.permission_code', '=', 'permissions.code')
            ->where('user_permissions.user_id', Session::get('session_userId'))
            ->select('permissions.*')
            ->get();

        return view('layouts.router', compact('title', 'user_permissions'));
    }

    public function getLogout()
    {
        Session::flush();
        Toastr::success('Successful logout', 'Success');
        return redirect()->route('login');
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
