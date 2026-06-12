<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('role'), fn($query) => $query->where('role', $request->role))
            ->when($request->filled('status'), fn($query) => $query->where('is_active', $request->status === 'active'))
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'mobile' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'language' => 'required|string|in:eng,guj,hin',
                'role' => 'required|string|in:admin,reporter,user',
                'password' => 'required_if:role,admin|nullable|string|min:6|confirmed',
            ]);

            $plainPassword = $request->role === 'admin' ? $request->password : '123456';

            $user = new User();
            $user->name = $request->name;
            $user->mobile = $request->mobile;
            $user->email = $request->email;
            $user->language = $request->language;
            $user->role = $request->role;
            $user->password = Hash::make($plainPassword);
            $user->save();
            return redirect()->route('admin.user.index')->with('success', 'User created successfully');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'User creation failed. Please try again later');
        }
    }

    public function show($id)
    {
        $user = User::find($id);
        return view('admin.users.show', compact('user'));
    }

    public function edit($id)
    {
        $user = User::find($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'language' => 'required|string|in:eng,guj,hin',
            'role' => 'required|string|in:admin,reporter,user',
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        $user = User::find($id);

        if (! $user) {
            return redirect()->route('admin.user.index')->with('error', 'User not found');
        }

        $updates = [
            'name' => $request->name,
            'mobile' => $request->mobile,
            'email' => $request->email,
            'language' => $request->language,
            'role' => $request->role,
        ];

        if ($request->role === 'admin') {
            if ($request->filled('password')) {
                $updates['password'] = Hash::make($request->password);
            }
        } else {
            if ($user->role === 'admin') {
                $updates['password'] = Hash::make('123456');
            }
        }

        $user->update($updates);

        return redirect()->route('admin.user.index')->with('success', 'User updated successfully');
    }

    public function destroy($id)
    {
        $user = User::find($id);

        if (! $user) {
            return redirect()->route('admin.user.index')->with('error', 'User not found');
        }

        if ($user->news()->exists()) {
            return redirect()->route('admin.user.index')->with('error', 'This user cannot be deleted because news records are linked to this account. Please delete or reassign the news first.');
        }

        DB::transaction(function () use ($user) {
            $user->favoriteCategories()->detach();
            $user->newsViews()->delete();
            $user->notifications()->delete();
            DB::table('user_bookmarks')->where('user_id', $user->id)->delete();
            $user->delete();
        });

        return redirect()->route('admin.user.index')->with('success', 'User deleted successfully');
    }
}
