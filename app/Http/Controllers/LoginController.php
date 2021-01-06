<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Brian2694\Toastr\Facades\Toastr;
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
                // Alert::warning("Error! ' . '' \n. $message.")->persistent('close');
                Toastr::error($message, 'Error!');
            }
            return back();
        }

        if (Auth::attempt(['username' => $request->username, 'password' =>  $request->password], $request->remember)) {
            // Authentication was successful...
            $user = User::findOrFail(Auth::id());
            if ($user->section == 'slaughter') {
                # slaughter user
                Toastr::success('Successful login','Success');
                return redirect()->route('slaughter_dashboard');
            }
            # butchery user
            Toastr::success('Successful login','Success');
            return redirect()->route('butchery_dashboard');
        }
        // failed login
        Toastr::warning('Wrong username or password. Please try again','Warning!');
        return back();

    }

    public function getLogout(Request $request)
    {
        Session::flush();
        Auth::logout();
        Toastr::success('Successful logout','Success');
        return redirect()->route('login');
    }
}
