<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
class LoginController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth')->except(['getLogin', 'processLogin']);
    }

    public function getLogin()
    {
        $computer_name = gethostname();
        return view('auth.login', compact('computer_name'));

    }

    public function processLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
        ]);


        if ($validator->fails()) {
            # failed validation
            $messages = $validator->errors();
            foreach ($messages->all() as $message) {
                Toastr::error($message, 'Error!');
            }
            return back();
        }

        $username_exists = User::where('username', $request->username)->value('username');
        if ($username_exists === null) {
            # username does not exist
            Toastr::warning('username provided does not exist.Contact Admin', 'Warning!');
            return back();
        }

        if (Auth::attempt(['username' => $request->username, 'password' =>  $request->password], $request->remember)) {
            // Authentication was successful...
            $user = User::findOrFail(Auth::id());

            //Check if session exists and log out the previous session
            $new_sessid   = \Session::getId(); //get new session_id after user sign in
            if($user->session != '') {
                $last_session = \Session::getHandler()->read($user->session);

                if ($last_session) {
                    if (\Session::getHandler()->destroy($user->session)) {

                    }
                }
            }
            $user->session = $new_sessid;
            $user->save();

            Toastr::success('Successful login','Success');
            return redirect()->intended('redirect_page');

        }
        // failed login
        Toastr::warning('Wrong username or password. Please try again','Warning!');
        return back();

    }

    public function getSectionRedirect()
    {
        $title = "Redirecting";
        return view('layouts.router', compact('title'));
    }

    public function getLogout()
    {
        Session::flush();
        Auth::logout();
        Toastr::success('Successful logout','Success');
        return redirect()->route('login');
    }
}
