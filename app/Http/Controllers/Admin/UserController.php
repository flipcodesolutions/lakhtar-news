<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Language;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('id', 'desc')->get();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $languages = Language::orderBy('id', 'desc')->get();
        return view('admin.users.create', compact('languages'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'language_id' => 'required|exists:languages,id',
            'role' => 'required|string|max:255',
        ]);
        User::create([
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'language_id' => $request->language_id,
            'role' => $request->role,
            'password' => Hash::make($request->password),
        ]);
        return redirect()->route('admin.user.index')->with('success', 'User created successfully');
    }

    public function show($id)
    {
        $user = User::find($id);
        return view('admin.users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::find($id);
        $languages = Language::orderBy('id', 'desc')->get();
        return view('admin.users.edit', compact('user', 'languages'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'language_id' => 'required|exists:languages,id',
            'role' => 'required|string|max:255',
        ]);
        $user = User::find($id);
        $user->update([
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'language_id' => $request->language_id,
            'role' => $request->role,
        ]);
        return redirect()->route('admin.user.index')->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        $user = User::find($id);
        $user->delete();
        return redirect()->route('admin.user.index')->with('success', 'User deleted successfully');
    }
}
