<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $mediaItems = Media::with(['news:id,title', 'uploader:id,name'])
            ->withCount('news')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('file_path', 'like', "%{$search}%")
                        ->orWhere('caption', 'like', "%{$search}%")
                        ->orWhereHas('news', fn($newsQuery) => $newsQuery->where('title', 'like', "%{$search}%"))
                        ->orWhereHas('uploader', fn($userQuery) => $userQuery->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('type'), fn($query) => $query->where('media_type', $request->input('type')))
            ->orderByDesc('id')
            ->paginate(16)
            ->withQueryString();

        return view('admin.media.index', compact('mediaItems'));
    }

    public function create()
    {
        return view('admin.media.create');
    }

    public function store(Request $request)
    {
        return view('admin.media.store');
    }

    public function edit($id)
    {
        return view('admin.media.edit');
    }

    public function update(Request $request, $id)
    {
        return view('admin.media.update');
    }

    public function destroy($id)
    {
        $media = Media::withCount('news')->find($id);

        if (! $media) {
            return redirect()->route('admin.media.index')->with('error', 'Media not found.');
        }

        if ($media->news_count > 0) {
            return redirect()->route('admin.media.index')->with('error', 'This media is linked to news and cannot be deleted.');
        }

        if ($media->media_type === 'image' && $media->file_path) {
            $absolutePath = public_path($media->file_path);

            if (File::exists($absolutePath)) {
                File::delete($absolutePath);
            }
        }

        $media->delete();

        return redirect()->route('admin.media.index')->with('success', 'Media deleted successfully.');
    }
}
