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
    public function index()
    {
        $news = News::orderBy('id', 'desc')->get();
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
            'video' => 'file|mimes:mp4,mov,avi|max:20480',
            'video_thumbnail' => 'required_if:video,file|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
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
        if ($request->hasFile('video_thumbnail')) {
            $file = $request->file('video_thumbnail');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('news'), $fileName);
            $news->video_thumbnail = 'news/' . $fileName;
        }
        if ($request->hasFile('video')) {
            $file = $request->file('video');
            $fileName = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('news'), $fileName);
            $news->video = 'news/' . $fileName;
        }
        $news->news_type = $request->news_type;
        $news->is_featured = $request->is_featured;
        $news->publish_date = $request->publish_date;
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
            'video' => 'file|mimes:mp4,mov,avi|max:20480',
            'video_thumbnail' => 'required_if:video,file|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
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
            if ($request->hasFile('video_thumbnail')) {
                $file = $request->file('video_thumbnail');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('news'), $fileName);
                $news->video_thumbnail = 'news/' . $fileName;
            }
            if ($request->hasFile('video')) {
                $file = $request->file('video');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('news'), $fileName);
                $news->video = 'news/' . $fileName;
            }
            $news->news_type = $request->news_type;
            $news->is_featured = $request->is_featured;
            $news->publish_date = $request->publish_date;
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
}
