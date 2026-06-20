<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Media;
use App\Models\News;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NewsController extends Controller
{
    public function index(Request $request)
    {
        $news = News::with(['category', 'user', 'media'])
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
    public function create(Request $request)
    {
        $categories = Category::where('status', 1)->get();
        $mediaLibrary = $this->getMediaLibrary();
        $preselectedMediaIds = collect($request->input('library_media', []))
            ->when(! is_array($request->input('library_media')), function ($collection) use ($request) {
                return $collection->push($request->input('library_media'));
            })
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();

        return view('admin.news.create', compact('categories', 'mediaLibrary', 'preselectedMediaIds'));
    }

    public function store(Request $request)
    {
        $this->validateNewsRequest($request);

        DB::transaction(function () use ($request) {
            $news = new News();
            $news->category_id = $request->category_id;
            $news->title = $request->title;
            $news->slug = Str::slug($request->title);
            $news->user_id = Auth::id();
            $news->description = $request->description;
            $news->titleInHindi = $request->titleInHindi;
            $news->descriptionInHindi = $request->descriptionInHindi;
            $news->titleInGujarati = $request->titleInGujarati;
            $news->descriptionInGujarati = $request->descriptionInGujarati;
            $news->news_type = $request->news_type;
            $news->is_featured = $request->boolean('is_featured');
            $news->publish_date = $request->publish_date;
            $news->status = 'approved';
            $news->save();

            $this->attachMediaToNews($news, $request);
        });

        return redirect()->route('admin.news.index')->with('success', 'News created successfully');
    }

    public function edit($id)
    {
        $news = News::with('media')->find($id);
        $categories = Category::where('status', 1)->get();
        $mediaLibrary = $this->getMediaLibrary($news);

        return view('admin.news.edit', compact('news', 'categories', 'mediaLibrary'));
    }

    public function update(Request $request)
    {
        $news = News::with('media')->find($request->id);

        if (! $news) {
            return redirect()->route('admin.news.index')->with('error', 'News not found.');
        }

        $this->validateNewsRequest($request, $news);

        DB::transaction(function () use ($request, $news) {
            $news->category_id = $request->category_id;
            $news->title = $request->title;
            $news->slug = Str::slug($request->title);
            $news->user_id = Auth::id();
            $news->description = $request->description;
            $news->titleInHindi = $request->titleInHindi;
            $news->descriptionInHindi = $request->descriptionInHindi;
            $news->titleInGujarati = $request->titleInGujarati;
            $news->descriptionInGujarati = $request->descriptionInGujarati;
            $news->news_type = $request->news_type;
            $news->is_featured = $request->boolean('is_featured');
            $news->publish_date = $request->publish_date;
            $news->status = 'approved';
            $news->save();

            $this->removeSelectedMedia($news, $request->input('remove_media_ids', []));
            $nextSortOrder = (int) ($news->media()->max('news_media.sort_order') ?? -1) + 1;
            $this->attachMediaToNews($news, $request, $nextSortOrder);
        });

        return redirect()->route('admin.news.index')->with('success', 'News updated successfully');
    }

    public function destroy($id)
    {
        $news = News::with('media')->find($id);
        if ($news) {
            $this->removeSelectedMedia($news, $news->media->pluck('id')->all());
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

        $news = News::with(['category', 'user', 'media'])
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
            ->paginate(10);

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

    private function validateNewsRequest(Request $request, ?News $news = null): void
    {
        $request->validate([
            'category_id' => 'required|integer|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'titleInHindi' => 'required|string|max:255',
            'descriptionInHindi' => 'required|string',
            'titleInGujarati' => 'required|string|max:255',
            'descriptionInGujarati' => 'required|string',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'video_urls' => 'nullable|array',
            'video_urls.*' => [
                'nullable',
                'url',
                function ($attribute, $value, $fail) {
                    if (blank(trim((string) $value))) {
                        return;
                    }

                    if (! $this->isYoutubeUrl($value)) {
                        $fail('Only YouTube video URLs are allowed.');
                    }
                },
            ],
            'selected_media_ids' => 'nullable|array',
            'selected_media_ids.*' => 'integer|exists:media,id',
            'remove_media_ids' => 'nullable|array',
            'remove_media_ids.*' => 'integer',
            'news_type' => 'required|string|in:general,breaking,trending,live',
            'is_featured' => 'required|boolean',
            'publish_date' => 'required|date',
        ]);

        $newImageCount = collect($request->file('images', []))->filter()->count();
        $newVideoCount = collect($request->input('video_urls', []))
            ->filter(fn($value) => filled(trim((string) $value)))
            ->count();

        $existingMediaCount = $news?->media->count() ?? 0;
        $removableIds = collect($request->input('remove_media_ids', []))
            ->map(fn($id) => (int) $id)
            ->intersect($news?->media->pluck('id') ?? collect())
            ->count();
        $selectedExistingCount = collect($request->input('selected_media_ids', []))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->diff($news?->media->pluck('id') ?? collect())
            ->count();

        if (($existingMediaCount - $removableIds + $newImageCount + $newVideoCount + $selectedExistingCount) <= 0) {
            throw ValidationException::withMessages([
                'images' => 'Add at least one image or YouTube video for this news.',
            ]);
        }
    }

    private function attachMediaToNews(News $news, Request $request, int $sortOrder = 0): void
    {
        $selectedMediaIds = collect($request->input('selected_media_ids', []))
            ->map(fn($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        if ($selectedMediaIds->isNotEmpty()) {
            $alreadyAttachedIds = $news->media()->pluck('media.id');

            foreach ($selectedMediaIds->diff($alreadyAttachedIds) as $mediaId) {
                $news->media()->attach((int) $mediaId, ['sort_order' => $sortOrder++]);
            }
        }

        foreach ($request->file('images', []) as $image) {
            if (! $image) {
                continue;
            }

            $fileName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('news'), $fileName);

            $media = Media::create([
                'media_type' => 'image',
                'file_path' => 'news/' . $fileName,
                'uploaded_by' => Auth::id(),
            ]);

            $news->media()->attach($media->id, ['sort_order' => $sortOrder++]);
        }

        foreach ($request->input('video_urls', []) as $videoUrl) {
            $videoUrl = trim((string) $videoUrl);

            if ($videoUrl === '') {
                continue;
            }

            $media = Media::create([
                'media_type' => 'video',
                'file_path' => $videoUrl,
                'uploaded_by' => Auth::id(),
            ]);

            $news->media()->attach($media->id, ['sort_order' => $sortOrder++]);
        }
    }

    private function removeSelectedMedia(News $news, array $mediaIds): void
    {
        $mediaIds = collect($mediaIds)
            ->map(fn($id) => (int) $id)
            ->filter()
            ->values()
            ->all();

        if ($mediaIds === []) {
            return;
        }

        $mediaItems = $news->media()
            ->whereIn('media.id', $mediaIds)
            ->get(['media.id', 'media.media_type', 'media.file_path']);

        $news->media()->detach($mediaItems->pluck('id')->all());

        foreach ($mediaItems as $media) {
            if ($media->news()->exists()) {
                continue;
            }

            if ($media->media_type === 'image' && $media->file_path) {
                $absolutePath = public_path($media->file_path);

                if (File::exists($absolutePath)) {
                    File::delete($absolutePath);
                }
            }

            $media->delete();
        }
    }

    private function isYoutubeUrl(string $url): bool
    {
        $host = strtolower((string) parse_url($url, PHP_URL_HOST));

        return in_array($host, [
            'youtube.com',
            'www.youtube.com',
            'm.youtube.com',
            'youtu.be',
            'www.youtu.be',
        ], true);
    }

    private function getMediaLibrary(?News $news = null)
    {
        return Media::with(['news:id,title', 'uploader:id,name'])
            ->withCount('news')
            ->when($news, function ($query) use ($news) {
                $query->whereDoesntHave('news', fn($newsQuery) => $newsQuery->where('news.id', $news->id));
            })
            ->orderByDesc('id')
            ->get();
    }
}
