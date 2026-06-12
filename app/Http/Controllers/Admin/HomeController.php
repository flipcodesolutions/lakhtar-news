<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\News;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class HomeController extends Controller
{
    public function dashboard()
    {
        $statusCounts = News::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $dashboardStats = [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('is_active', true)->count(),
            'categories' => Category::where('status', true)->count(),
            'totalNews' => News::count(),
            'featuredNews' => News::where('is_featured', true)->count(),
            'scheduledNews' => News::whereNotNull('publish_date')
                ->where('publish_date', '>', now())
                ->count(),
            'totalViews' => News::sum('total_views'),
            'pendingNews' => (int) ($statusCounts['pending'] ?? 0),
            'approvedNews' => (int) ($statusCounts['approved'] ?? 0),
            'rejectedNews' => (int) ($statusCounts['rejected'] ?? 0),
        ];

        // without admin created news 
        $recentNews = News::with(['category:id,name', 'user:id,name'])
            ->where('user_id', '<>', 1)
            ->latest()
            ->take(5)
            ->get();

        $topCategories = Category::query()
            ->withCount('news')
            ->orderByDesc('news_count')
            ->take(5)
            ->get();

        $popularNews = News::with(['category:id,name'])
            ->orderByDesc('total_views')
            ->take(5)
            ->get();

        return view('admin.dashboard.dashboard', compact(
            'dashboardStats',
            'recentNews',
            'topCategories',
            'popularNews'
        ));
    }

    public function passwordChange()
    {
        return view('admin.login.changePassword');
    }

    public function passwordUpdate(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (! $user) {
            return redirect()->route('admin.login')->with('error', 'Unauthenticated.');
        }

        if (! Hash::check($request->current_password, (string) $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Current password is incorrect.',
            ]);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('admin.password.change')->with('success', 'Password updated successfully.');
    }

    public function profile()
    {
        $user = Auth::user();

        return view('admin.profile.index', compact('user'));
    }

    public function profileUpdate(Request $request)
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('admin.login')->with('error', 'Unauthenticated.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'mobile' => [
                'required',
                'regex:/^[0-9]{10}$/',
                Rule::unique('users', 'mobile')->ignore($user->id),
            ],
            'language' => 'required|string|in:eng,guj,hin',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->mobile = $request->mobile;
        $user->language = $request->language;

        if ($request->hasFile('profile_image')) {
            if ($user->profile_image) {
                $oldImagePath = public_path($user->profile_image);
                if (File::exists($oldImagePath)) {
                    File::delete($oldImagePath);
                }
            }

            $file = $request->file('profile_image');
            $fileName = time() . '_' . $user->id . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('profile'), $fileName);
            $user->profile_image = 'profile/' . $fileName;
        }

        $user->save();

        return redirect()->route('admin.profile')->with('success', 'Profile updated successfully.');
    }
}
