<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    public function index(Request $request)
    {
        $banners = Banner::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');

                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('title', 'like', "%{$search}%")
                        ->orWhere('link', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $status = $request->input('status');
                if ($status === 'active') {
                    $query->where('status', true);
                } elseif ($status === 'inactive') {
                    $query->where('status', false);
                }
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.banner.index', compact('banners'));
    }

    public function create()
    {
        return view('admin.banner.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'link' => 'nullable|url|max:2000',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|boolean',
        ]);

        $banner = new Banner();
        $banner->title = $request->title;
        $banner->link = $request->link;
        $banner->start_date = $request->start_date;
        $banner->end_date = $request->end_date;
        $banner->status = $request->boolean('status');

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('banners'), $fileName);
            $banner->image = 'banners/' . $fileName;
        }

        $banner->save();

        return redirect()->route('admin.banner.index')->with('success', 'Banner created successfully.');
    }

    public function edit($id)
    {
        $banner = Banner::find($id);

        if (! $banner) {
            return redirect()->route('admin.banner.index')->with('error', 'Banner not found.');
        }

        return view('admin.banner.edit', compact('banner'));
    }

    public function update(Request $request, $id)
    {
        $banner = Banner::find($id);

        if (! $banner) {
            return redirect()->route('admin.banner.index')->with('error', 'Banner not found.');
        }

        $request->validate([
            'title' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'link' => 'nullable|url|max:2000',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|boolean',
        ]);

        $banner->title = $request->title;
        $banner->link = $request->link;
        $banner->start_date = $request->start_date;
        $banner->end_date = $request->end_date;
        $banner->status = $request->boolean('status');

        if ($request->hasFile('image')) {
            if ($banner->image) {
                $oldPath = public_path($banner->image);
                if (File::exists($oldPath)) {
                    File::delete($oldPath);
                }
            }

            $file = $request->file('image');
            $fileName = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('banners'), $fileName);
            $banner->image = 'banners/' . $fileName;
        }

        $banner->save();

        return redirect()->route('admin.banner.index')->with('success', 'Banner updated successfully.');
    }

    public function destroy($id)
    {
        $banner = Banner::find($id);

        if (! $banner) {
            return redirect()->route('admin.banner.index')->with('error', 'Banner not found.');
        }

        if ($banner->image) {
            $imagePath = public_path($banner->image);
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }

        $banner->delete();

        return redirect()->route('admin.banner.index')->with('success', 'Banner deleted successfully.');
    }
}
