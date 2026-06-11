<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\News;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReporterController extends Controller
{
    /**
     * @OA\Get(
     *     path="/my-news",
     *     tags={"Reporter"},
     *     summary="Get Reporter News",
     *     description="Fetch all news created by the authenticated reporter. News title and description are returned according to the user's selected language.",
     *     operationId="getReporterNews",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Reporter news fetched successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Reporter news fetched successfully in English"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="news",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(
     *                             property="title",
     *                             type="string",
     *                             example="Breaking News Title"
     *                         ),
     *                         @OA\Property(
     *                             property="description",
     *                             type="string",
     *                             example="This is the news description."
     *                         ),
     *                         @OA\Property(
     *                             property="slug",
     *                             type="string",
     *                             example="breaking-news-title"
     *                         ),
     *                         @OA\Property(
     *                             property="image",
     *                             type="string",
     *                             example="news/1718100000_abcd1234.webp"
     *                         ),
     *                         @OA\Property(
     *                             property="video",
     *                             type="string",
     *                             nullable=true,
     *                             example="https://www.youtube.com/watch?v=dQw4w9WgXcQ"
     *                         ),
     *                         @OA\Property(
     *                             property="media",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=10),
     *                                 @OA\Property(property="media_type", type="string", example="image"),
     *                                 @OA\Property(property="file_path", type="string", example="news/1718100000_abcd1234.webp"),
     *                                 @OA\Property(property="thumbnail", type="string", nullable=true, example=null),
     *                                 @OA\Property(property="caption", type="string", nullable=true, example=null),
     *                                 @OA\Property(property="sort_order", type="integer", example=0)
     *                             )
     *                         ),
     *                         @OA\Property(
     *                             property="news_type",
     *                             type="string",
     *                             example="breaking"
     *                         ),
     *                         @OA\Property(
     *                             property="status",
     *                             type="string",
     *                             example="published"
     *                         ),
     *                         @OA\Property(
     *                             property="created_at",
     *                             type="string",
     *                             format="date-time",
     *                             example="2026-06-09T08:30:00.000000Z"
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong."
     *             )
     *         )
     *     )
     * )
     */
    // Get my news
    public function getMyNews(Request $request)
    {
        try {

            $language = Auth::user()?->language ?? 'eng';

            $titleColumn = match ($language) {
                'hin' => 'titleInHindi',
                'guj' => 'titleInGujarati',
                default => 'title',
            };

            $descriptionColumn = match ($language) {
                'hin' => 'descriptionInHindi',
                'guj' => 'descriptionInGujarati',
                default => 'description',
            };

            $message = match ($language) {
                'hin' => 'रिपोर्टर की खबरें सफलतापूर्वक प्राप्त कर ली गईं।',
                'guj' => 'રિપોર્ટર સમાચાર સફળતાપૂર્વક મેળવ્યા.',
                default => 'Reporter news fetched successfully',
            };

            $news = News::with('media')
                ->where('user_id', Auth::id())
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($item) use ($titleColumn, $descriptionColumn) {
                    $media = $item->media->map(function ($mediaItem) {
                        return [
                            'id' => $mediaItem->id,
                            'media_type' => $mediaItem->media_type,
                            'file_path' => $mediaItem->file_path,
                            'thumbnail' => $mediaItem->thumbnail,
                            'caption' => $mediaItem->caption,
                            'sort_order' => $mediaItem->pivot?->sort_order,
                        ];
                    })->values();

                    return [
                        'id' => $item->id,
                        'title' => $item->$titleColumn,
                        'description' => $item->$descriptionColumn,
                        'slug' => $item->slug,
                        'image' => $item->image,
                        'video' => $item->video,
                        'media' => $media,
                        'news_type' => $item->news_type,
                        'status' => $item->status,
                        'created_at' => $item->created_at,
                    ];
                });

            return Util::getSuccessMessage(
                $message,
                [
                    'news' => $news
                ]
            );
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/create-news",
     *     tags={"Reporter"},
     *     summary="Create News",
     *     description="Create a new news article with multilingual content, multiple images and YouTube video URLs.",
     *     operationId="createNews",
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={
     *                     "category_id",
     *                     "title",
     *                     "description",
     *                     "titleInHindi",
     *                     "descriptionInHindi",
     *                     "titleInGujarati",
     *                     "descriptionInGujarati",
     *                     "news_type",
     *                     "is_featured",
     *                     "publish_date"
     *                 },
     *
     *                 @OA\Property(
     *                     property="category_id",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                     example="Breaking News"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     example="English news description"
     *                 ),
     *                 @OA\Property(
     *                     property="titleInHindi",
     *                     type="string",
     *                     example="ब्रेकिंग न्यूज़"
     *                 ),
     *                 @OA\Property(
     *                     property="descriptionInHindi",
     *                     type="string",
     *                     example="हिंदी समाचार विवरण"
     *                 ),
     *                 @OA\Property(
     *                     property="titleInGujarati",
     *                     type="string",
     *                     example="બ્રેકિંગ ન્યૂઝ"
     *                 ),
     *                 @OA\Property(
     *                     property="descriptionInGujarati",
     *                     type="string",
     *                     example="ગુજરાતી સમાચાર વર્ણન"
     *                 ),
     *                 @OA\Property(
     *                     property="images",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Multiple images"
     *                 ),
     *                 @OA\Property(
     *                     property="video_urls",
     *                     type="array",
     *                     @OA\Items(type="string", format="url"),
     *                     description="YouTube video URLs"
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Deprecated. Use images[]"
     *                 ),
     *                 @OA\Property(
     *                     property="video",
     *                     type="string",
     *                     format="url",
     *                     description="Deprecated. Use video_urls[] (YouTube only)"
     *                 ),
     *                 @OA\Property(
     *                     property="news_type",
     *                     type="string",
     *                     enum={"normal","breaking","trending","live"},
     *                     example="breaking"
     *                 ),
     *                 @OA\Property(
     *                     property="is_featured",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="publish_date",
     *                     type="string",
     *                     format="date",
     *                     example="2026-06-09"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Reporter news created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Reporter news created successfully"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */

    public function createNews(Request $request)
    {
        $language = Auth::user()?->language ?? 'eng';
        $message = match ($language) {
            'hin' => 'रिपोर्टर न्यूज़ सफलतापूर्वक बनाई गई।',
            'guj' => 'રિપોર્ટર સમાચાર સફળતાપૂર્વક બનાવવામાં આવ્યા',
            default => 'Reporter news created successfully',
        };
        try {
            $this->validateNewsRequest($request);

            $newsId = null;

            DB::transaction(function () use ($request, &$newsId) {
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
                $news->save();

                $this->attachMediaToNews($news, $request);
                $newsId = $news->id;
            });

            return Util::getSuccessMessage($message);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    /**
     * @OA\Put(
     *     path="/update-news/{id}",
     *     tags={"Reporter"},
     *     summary="Update News",
     *     description="Update an existing news article. Supports adding/removing media. Videos must be YouTube URLs.",
     *     operationId="updateNews",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="News ID",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={
     *                     "category_id",
     *                     "title",
     *                     "description",
     *                     "titleInHindi",
     *                     "descriptionInHindi",
     *                     "titleInGujarati",
     *                     "descriptionInGujarati",
     *                     "news_type",
     *                     "is_featured",
     *                     "publish_date"
     *                 },
     *
     *                 @OA\Property(
     *                     property="category_id",
     *                     type="integer",
     *                     example=1
     *                 ),
     *                 @OA\Property(
     *                     property="title",
     *                     type="string",
     *                     example="Updated Breaking News"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string",
     *                     example="Updated English description"
     *                 ),
     *                 @OA\Property(
     *                     property="titleInHindi",
     *                     type="string",
     *                     example="अपडेटेड ब्रेकिंग न्यूज़"
     *                 ),
     *                 @OA\Property(
     *                     property="descriptionInHindi",
     *                     type="string",
     *                     example="अपडेटेड हिंदी विवरण"
     *                 ),
     *                 @OA\Property(
     *                     property="titleInGujarati",
     *                     type="string",
     *                     example="અપડેટેડ બ્રેકિંગ ન્યૂઝ"
     *                 ),
     *                 @OA\Property(
     *                     property="descriptionInGujarati",
     *                     type="string",
     *                     example="અપડેટેડ ગુજરાતી વર્ણન"
     *                 ),
     *                 @OA\Property(
     *                     property="images",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Add multiple images"
     *                 ),
     *                 @OA\Property(
     *                     property="video_urls",
     *                     type="array",
     *                     @OA\Items(type="string", format="url"),
     *                     description="Add YouTube video URLs"
     *                 ),
     *                 @OA\Property(
     *                     property="remove_media_ids",
     *                     type="array",
     *                     @OA\Items(type="integer"),
     *                     description="Media IDs to remove from this news"
     *                 ),
     *                 @OA\Property(
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="Deprecated. Use images[]"
     *                 ),
     *                 @OA\Property(
     *                     property="video",
     *                     type="string",
     *                     format="url",
     *                     description="Deprecated. Use video_urls[] (YouTube only)"
     *                 ),
     *                 @OA\Property(
     *                     property="news_type",
     *                     type="string",
     *                     enum={"normal","breaking","trending","live"},
     *                     example="breaking"
     *                 ),
     *                 @OA\Property(
     *                     property="is_featured",
     *                     type="boolean",
     *                     example=true
     *                 ),
     *                 @OA\Property(
     *                     property="publish_date",
     *                     type="string",
     *                     format="date",
     *                     example="2026-06-09"
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Reporter news updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Reporter news updated successfully"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="News not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="News not found"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error"
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */

    public function updateNews(Request $request, $id)
    {

        $news = News::with('media')->where('id', $id)->where('user_id', Auth::id())->first();
        if (! $news) {
            return Util::getErrorMessage('News not found');
        }
        $language = Auth::user()?->language ?? 'eng';
        $message = match ($language) {
            'hin' => 'रिपोर्टर की खबर सफलतापूर्वक अपडेट हो गई।',
            'guj' => 'રિપોર્ટર સમાચાર સફળતાપૂર્વક અપડેટ થયા',
            default => 'Reporter news updated successfully',
        };
        try {

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
                $news->save();

                $this->removeSelectedMedia($news, $request->input('remove_media_ids', []));
                $nextSortOrder = (int) ($news->media()->max('news_media.sort_order') ?? -1) + 1;
                $this->attachMediaToNews($news, $request, $nextSortOrder);
            });

            return Util::getSuccessMessage($message);
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    /**
     * @OA\Delete(
     *     path="/delete-news/{id}",
     *     tags={"Reporter"},
     *     summary="Delete News",
     *     description="Delete a news article created by the authenticated reporter.",
     *     operationId="deleteNews",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="News ID",
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Reporter news deleted successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Reporter news deleted successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items()
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="News not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="News not found"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Unauthenticated."
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Something went wrong."
     *             )
     *         )
     *     )
     * )
     */

    public function deleteNews($id)
    {
        $userId = Auth::user()->id;
        $language = Auth::user()?->language ?? 'eng';

        $message = match ($language) {
            'hin' => 'रिपोर्टर न्यूज़ सफलतापूर्वक हटा दी गई।',
            'guj' => 'રિપોર્ટર સમાચાર સફળતાપૂર્વક કાઢી નાખવામાં આવ્યા',
            default => 'Reporter news deleted successfully',
        };

        $news = News::with('media')->where('id', $id)->where('user_id', $userId)->first();
        if (!$news) {
            return Util::getErrorMessage('News not found');
        }
        $this->removeSelectedMedia($news, $news->media->pluck('id')->all());
        $news->delete();
        return Util::getSuccessMessage($message);
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'images' => 'nullable|array',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'video' => 'nullable|url',
            'video_urls' => 'nullable|array',
            'video_urls.*' => [
                'nullable',
                'url',
                function ($attribute, $value, $fail) {
                    if (blank(trim((string) $value))) {
                        return;
                    }

                    if (! $this->isYoutubeUrl((string) $value)) {
                        $fail('Only YouTube video URLs are allowed.');
                    }
                },
            ],
            'remove_media_ids' => 'nullable|array',
            'remove_media_ids.*' => 'integer',
            'news_type' => 'required|string|in:normal,breaking,trending,live',
            'is_featured' => 'required|boolean',
            'publish_date' => 'required|date',
        ]);

        $newImages = collect($request->file('images', []))->filter();
        $singleImage = $request->file('image');
        if ($singleImage) {
            $newImages->push($singleImage);
        }
        $newImageCount = $newImages->count();

        $videoUrls = collect($request->input('video_urls', []))->filter(fn($value) => filled(trim((string) $value)));
        $singleVideo = trim((string) $request->input('video', ''));
        if ($singleVideo !== '') {
            $videoUrls->push($singleVideo);
        }
        $newVideoCount = $videoUrls->count();

        $existingMediaCount = $news?->media->count() ?? 0;
        $removableIds = collect($request->input('remove_media_ids', []))
            ->map(fn($id) => (int) $id)
            ->intersect($news?->media->pluck('id') ?? collect())
            ->count();

        if (($existingMediaCount - $removableIds + $newImageCount + $newVideoCount) <= 0) {
            throw ValidationException::withMessages([
                'images' => 'Add at least one image or YouTube video for this news.',
            ]);
        }
    }

    private function attachMediaToNews(News $news, Request $request, int $sortOrder = 0): void
    {
        $images = collect($request->file('images', []))->filter();
        $singleImage = $request->file('image');
        if ($singleImage) {
            $images->push($singleImage);
        }

        foreach ($images as $image) {
            $fileName = time() . '_' . Str::random(10) . '.' . $image->getClientOriginalExtension();
            $image->move(public_path('news'), $fileName);

            $media = Media::create([
                'media_type' => 'image',
                'file_path' => 'news/' . $fileName,
                'uploaded_by' => Auth::id(),
            ]);

            $news->media()->attach($media->id, ['sort_order' => $sortOrder++]);
        }

        $videoUrls = collect($request->input('video_urls', []))->filter(fn($value) => filled(trim((string) $value)));
        $singleVideo = trim((string) $request->input('video', ''));
        if ($singleVideo !== '') {
            $videoUrls->push($singleVideo);
        }

        foreach ($videoUrls as $videoUrl) {
            $videoUrl = trim((string) $videoUrl);

            if ($videoUrl === '') {
                continue;
            }

            if (! $this->isYoutubeUrl($videoUrl)) {
                throw ValidationException::withMessages([
                    'video_urls' => 'Only YouTube video URLs are allowed.',
                ]);
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
}
