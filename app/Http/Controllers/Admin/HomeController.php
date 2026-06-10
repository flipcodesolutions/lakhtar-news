<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\News;
use App\Models\User;

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

        $recentNews = News::with(['category:id,name', 'user:id,name'])
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
}
