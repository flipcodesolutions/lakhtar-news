<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function login()
    {
        return view('admin.login.login');
    }

    public function loginPost(Request $request)
    {
        $request->validate([
            'email' => 'required|string|max:255',
            'password' => 'required|string|max:255',
        ]);
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return redirect()->back()->withInput()->with('error', 'Email not found');
        }
        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()->withInput()->with('error', 'Invalid password');
        }
        Auth::login($user);
        return redirect()->intended(route('admin.dashboard'));
    }
}
