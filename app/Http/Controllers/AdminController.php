<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    public function __construct()
    {
        // $this->middleware('auth');
    }

    public function index()
    {
        $title = "Dashboard";
        return view('admin.dashboard', compact('title'));
    }

    public function getUsers()
    {
        $title = "Users";
        $users = DB::table('users')
            ->select('id', 'username', 'email', 'section', 'created_at')
            ->get();
        return view('admin.users', compact('title', 'users'));
    }

    public function addUser(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users,username',
            'email' => 'required|unique:users,email',
            'section' => 'required',

        ]);

        if ($validator->fails()) {
            # failed validation
            return back()
                ->with('input_errors', 'add_user')
                ->withErrors($validator)
                ->withInput();
        }
        dd('passed');
        $maker = Auth::id();
    }

    public function changePassword()
    {
        $title = "Password";
        return view('admin.change_password', compact('title'));
    }
}
