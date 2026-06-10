<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $news = News::with(['category', 'user'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('titleInHindi', 'like', "%{$search}%")
                        ->orWhere('titleInGujarati', 'like', "%{$search}%")
                        ->orWhereHas('category', fn($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn($query) => $query->where('status', $request->status))
            ->when($request->filled('news_type'), fn($query) => $query->where('news_type', $request->news_type))
            ->when($request->filled('featured'), function ($query) use ($request) {
                $query->where('is_featured', $request->featured === 'yes');
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('admin.news.index', compact('news'));
    }
    public function create()
    {
        $categories = Category::where('status', 1)->get();
        return view('admin.news.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'titleInHindi' => 'required|string|max:255',
            'descriptionInHindi' => 'required|string',
            'titleInGujarati' => 'required|string|max:255',
            'descriptionInGujarati' => 'required|string',
            'image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'video' => 'nullable|string',
            'news_type' => 'required|string|in:normal,breaking,trending,live',
            'is_featured' => 'required|boolean',
            'publish_date' => 'required|date',
        ]);

        $news = new News();
        $news->category_id = $request->category_id;
        $news->title = $request->title;
        $news->slug = Str::slug($request->title);
        $news->user_id = Auth::user()->id;
        $news->description = $request->description;
        $news->titleInHindi = $request->titleInHindi;
        $news->descriptionInHindi = $request->descriptionInHindi;
        $news->titleInGujarati = $request->titleInGujarati;
        $news->descriptionInGujarati = $request->descriptionInGujarati;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('news'), $fileName);
            $news->image = 'news/' . $fileName;
        }
        $news->video = $request->video;
        $news->news_type = $request->news_type;
        $news->is_featured = $request->is_featured;
        $news->publish_date = $request->publish_date;
        $news->status = 'approved';
        $news->save();
        return redirect()->route('admin.news.index')->with('success', 'News created successfully');
    }

    public function edit($id)
    {
        $news = News::find($id);
        $categories = Category::where('status', 1)->get();
        return view('admin.news.edit', compact('news', 'categories'));
    }

    public function update(Request $request)
    {

        $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'titleInHindi' => 'required|string|max:255',
            'descriptionInHindi' => 'required|string',
            'titleInGujarati' => 'required|string|max:255',
            'descriptionInGujarati' => 'required|string',
            'image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'video' => 'nullable|string',
            'news_type' => 'required|string|in:normal,breaking,trending,live',
            'is_featured' => 'required|boolean',
            'publish_date' => 'required|date',
        ]);

        $news = News::find($request->id);
        if ($news) {
            $news->category_id = $request->category_id;
            $news->title = $request->title;
            $news->slug = Str::slug($request->title);
            $news->user_id = Auth::user()->id;
            $news->description = $request->description;
            $news->titleInHindi = $request->titleInHindi;
            $news->descriptionInHindi = $request->descriptionInHindi;
            $news->titleInGujarati = $request->titleInGujarati;
            $news->descriptionInGujarati = $request->descriptionInGujarati;

            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('news'), $fileName);
                $news->image = 'news/' . $fileName;
            }
            $news->video = $request->video;
            $news->news_type = $request->news_type;
            $news->is_featured = $request->is_featured;
            $news->publish_date = $request->publish_date;
            $news->status = 'approved';
            $news->save();
            return redirect()->route('admin.news.index')->with('success', 'News updated successfully');
        }
    }

    public function destroy($id)
    {
        $news = News::find($id);
        if ($news) {
            $news->delete();
            return redirect()->route('admin.news.index')->with('success', 'News deleted successfully');
        }
    }

    public function reporterIndex(Request $request)
    {
        $selectedStatus = $request->input('status', 'pending');

        if (! in_array($selectedStatus, ['pending', 'approved', 'rejected'], true)) {
            $selectedStatus = 'pending';
        }

        $news = News::with(['category', 'user'])
            ->whereHas('user', fn($query) => $query->where('role', '!=', 'admin'))
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('title', 'like', "%{$search}%")
                        ->orWhere('titleInHindi', 'like', "%{$search}%")
                        ->orWhere('titleInGujarati', 'like', "%{$search}%")
                        ->orWhereHas('category', fn($categoryQuery) => $categoryQuery->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('user', fn($userQuery) => $userQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%"));
                });
            })
            ->where('status', $selectedStatus)
            ->orderByDesc('id')
            ->get();

        return view('admin.reporter-news.index', compact('news', 'selectedStatus'));
    }

    public function changeStatus($id, $status)
    {
        if (! in_array($status, ['approved', 'rejected'], true)) {
            return redirect()->route('admin.reporter-news.index')->with('error', 'Invalid status.');
        }

        $news = News::find($id);

        if (! $news) {
            return redirect()->route('admin.reporter-news.index')->with('error', 'News not found.');
        }

        $news->status = $status;
        $news->save();

        return redirect()->route('admin.reporter-news.index')->with('success', 'News status updated successfully.');
    }
}
