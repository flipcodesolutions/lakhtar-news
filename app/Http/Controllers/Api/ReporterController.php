<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\News;
use App\Utils\Util;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class ReporterController extends Controller
{
    /**
     * @OA\Get(
     *     path="/my-news",
     *     tags={"Reporter"},
     *     summary="Get Reporter News",
     *     description="Fetch all news created by the authenticated reporter. Optionally filter news by status (pending, approved, rejected). News title and description are returned according to the user's selected language.",
     *     operationId="getReporterNews",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         description="Filter news by status",
     *         @OA\Schema(
     *             type="string",
     *             enum={"pending","approved","rejected"},
     *             example="---"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Reporter news fetched successfully.",
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Reporter news fetched successfully"
     *             ),
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(
     *                     property="news",
     *                     type="array",
     *
     *                     @OA\Items(
     *                         type="object",
     *
     *                         @OA\Property(
     *                             property="id",
     *                             type="integer",
     *                             example=1
     *                         ),
     *
     *                         @OA\Property(
     *                             property="title",
     *                             type="string",
     *                             example="Breaking News Title"
     *                         ),
     *
     *                         @OA\Property(
     *                             property="description",
     *                             type="string",
     *                             example="This is the news description."
     *                         ),
     *
     *                         @OA\Property(
     *                             property="slug",
     *                             type="string",
     *                             example="breaking-news-title"
     *                         ),
     *
     *                         @OA\Property(
     *                             property="image",
     *                             type="string",
     *                             nullable=true,
     *                             example="news/1718100000_abcd1234.webp"
     *                         ),
     *
     *                         @OA\Property(
     *                             property="video",
     *                             type="string",
     *                             nullable=true,
     *                             example="https://www.youtube.com/watch?v=dQw4w9WgXcQ"
     *                         ),
     *
     *                         @OA\Property(
     *                             property="media",
     *                             type="array",
     *
     *                             @OA\Items(
     *                                 type="object",
     *
     *                                 @OA\Property(
     *                                     property="id",
     *                                     type="integer",
     *                                     example=10
     *                                 ),
     *
     *                                 @OA\Property(
     *                                     property="media_type",
     *                                     type="string",
     *                                     example="image"
     *                                 ),
     *
     *                                 @OA\Property(
     *                                     property="file_path",
     *                                     type="string",
     *                                     example="news/1718100000_abcd1234.webp"
     *                                 ),
     *
     *                                 @OA\Property(
     *                                     property="thumbnail",
     *                                     type="string",
     *                                     nullable=true,
     *                                     example=null
     *                                 ),
     *
     *                                 @OA\Property(
     *                                     property="caption",
     *                                     type="string",
     *                                     nullable=true,
     *                                     example=null
     *                                 ),
     *
     *                                 @OA\Property(
     *                                     property="sort_order",
     *                                     type="integer",
     *                                     example=0
     *                                 )
     *                             )
     *                         ),
     *
     *                         @OA\Property(
     *                             property="news_type",
     *                             type="string",
     *                             example="breaking"
     *                         ),
     *
     *                         @OA\Property(
     *                             property="status",
     *                             type="string",
     *                             example="pending"
     *                         ),
     *
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
                ->when($request->filled('status'), function ($query) use ($request) {
                    $query->where('status', $request->status);
                })
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
     * @OA\Get(
     *     path="/my-news/{id}",
     *     summary="Get My News Details",
     *     description="Retrieve details of a specific news item created by the authenticated reporter, including associated media.",
     *     operationId="getMyNewsDetails",
     *     tags={"Reporter News"},
     *     security={{"bearerAuth":{}}},
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
     *         description="Reporter news details fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Reporter news details fetched successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="news",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Breaking News"),
     *                     @OA\Property(property="description", type="string", example="News description"),
     *                     @OA\Property(property="user_id", type="integer", example=5),
     *                     @OA\Property(
     *                         property="media",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=10),
     *                             @OA\Property(property="media_url", type="string", example="https://example.com/image.jpg"),
     *                             @OA\Property(property="media_type", type="string", example="image")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="News not found or does not belong to the authenticated user"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error"
     *     )
     * )
     */
    public function getMyNewsDetails($id)
    {
        try {
            $language = Auth::user()?->language ?? 'guj';
            $message = match ($language) {
                'hin' => 'रिपोर्टर की खबरें सफलतापूर्वक प्राप्त कर ली गईं।',
                'guj' => 'રિપોર્ટર સમાચાર સફળતાપૂર્વક મેળવ્યા.',
                default => 'Reporter news details fetched successfully',
            };

            $news = News::with('media')
                ->where('id', $id)
                ->where('user_id', Auth::id())
                ->firstOrFail();

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
     *                     property="images[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Multiple images (send as images[])"
     *                 ),
     *                 @OA\Property(
     *                     property="video_urls[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="url"),
     *                     description="YouTube video URLs (send as video_urls[])"
     *                 ),
     *                 @OA\Property(
     *                     property="news_type",
     *                     type="string",
     *                     enum={"general","breaking","trending","live"},
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
                $news->slug = $this->generateUniqueSlug($request->title);
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
     *                     property="images[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="binary"),
     *                     description="Add multiple images (send as images[])"
     *                 ),
     *                 @OA\Property(
     *                     property="video_urls[]",
     *                     type="array",
     *                     @OA\Items(type="string", format="url"),
     *                     description="Add YouTube video URLs (send as video_urls[])"
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
     *                     description="Deprecated. Use images"
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
     *                     enum={"general","breaking","trending","live"},
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
                $news->slug = $this->generateUniqueSlug($request->title, $news->id);
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
            'images' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    $files = $this->extractUploadedImages($request);

                    if (count($files) === 1) {
                        $validator = Validator::make(
                            ['file' => $files[0]],
                            ['file' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048']
                        );

                        if ($validator->fails()) {
                            $fail($validator->errors()->first('file'));
                        }
                    }
                },
            ],
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'video' => 'nullable|url',
            'video_urls' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value === null) {
                        return;
                    }

                    if (is_string($value)) {
                        $urls = $this->normalizeVideoUrls($value);
                        if ($urls === []) {
                            return;
                        }

                        foreach ($urls as $url) {
                            if (! filter_var($url, FILTER_VALIDATE_URL)) {
                                $fail('The video urls field must be a valid URL.');
                                return;
                            }

                            if (! $this->isYoutubeUrl($url)) {
                                $fail('Only YouTube video URLs are allowed.');
                                return;
                            }
                        }

                        return;
                    }

                    if (! is_array($value)) {
                        $fail('The video urls field must be an array.');
                    }
                },
            ],
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
            'news_type' => 'required|string|in:general,breaking,trending,live',
            'is_featured' => 'required|boolean',
            'publish_date' => 'required|date',
        ]);

        $newImages = collect($this->extractUploadedImages($request))->filter();
        $singleImage = $request->file('image');
        if ($singleImage) {
            $newImages->push($singleImage);
        }
        $newImageCount = $newImages->count();

        $videoUrls = collect($this->extractVideoUrls($request));
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
        $images = collect($this->extractUploadedImages($request))->filter();
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

        $videoUrls = collect($this->extractVideoUrls($request))
            ->flatMap(fn($value) => $this->normalizeVideoUrls($value))
            ->filter(fn($value) => filled(trim((string) $value)))
            ->unique()
            ->values();
        $singleVideo = trim((string) $request->input('video', ''));
        if ($singleVideo !== '') {
            $videoUrls = $videoUrls
                ->merge($this->normalizeVideoUrls($singleVideo))
                ->filter(fn($value) => filled(trim((string) $value)))
                ->unique()
                ->values();
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

    private function extractUploadedImages(Request $request): array
    {
        return array_values(array_filter(array_merge(
            $this->normalizeUploadedFiles($request->file('images')),
            $this->normalizeUploadedFiles($request->file('images[]'))
        )));
    }

    private function extractVideoUrls(Request $request): array
    {
        $urls = array_values(array_filter(array_merge(
            $this->normalizeVideoUrls($request->input('video_urls')),
            $this->normalizeVideoUrls($request->input('video_urls[]')),
            $this->normalizeVideoUrls($request->all()['video_urls[]'] ?? null)
        ), fn($item) => filled(trim((string) $item))));

        return array_values(array_unique($urls));
    }

    private function normalizeUploadedFiles(mixed $files): array
    {
        if ($files instanceof UploadedFile) {
            return [$files];
        }

        if (is_array($files)) {
            return array_values(array_filter($files));
        }

        return [];
    }

    private function normalizeVideoUrls(mixed $value): array
    {
        if (is_array($value)) {
            $parts = [];

            foreach ($value as $item) {
                $parts = array_merge($parts, $this->normalizeVideoUrls($item));
            }

            return array_values(array_filter($parts, fn($item) => filled(trim((string) $item))));
        }

        if (! is_string($value)) {
            return [];
        }

        $value = trim(str_replace("\r", "\n", $value));
        if ($value === '') {
            return [];
        }

        $matches = [];
        preg_match_all('~https?://[^\s,]+~i', $value, $matches);
        if (! empty($matches[0])) {
            return array_values(array_filter(array_map('trim', $matches[0])));
        }

        $parts = [];
        foreach (explode("\n", $value) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            foreach (preg_split('/\s*,\s*/', $line) as $segment) {
                $segment = trim($segment);
                if ($segment !== '') {
                    $parts[] = $segment;
                }
            }
        }

        return $parts;
    }

    private function generateUniqueSlug(string $title, ?int $ignoreNewsId = null): string
    {
        $base = Str::slug($title);
        $base = $base !== '' ? $base : 'news';

        $query = News::query()->where('slug', $base);
        if ($ignoreNewsId !== null) {
            $query->where('id', '!=', $ignoreNewsId);
        }

        if (! $query->exists()) {
            return $base;
        }

        $suffix = 2;
        while (true) {
            $candidate = $base . '-' . $suffix;
            $candidateQuery = News::query()->where('slug', $candidate);
            if ($ignoreNewsId !== null) {
                $candidateQuery->where('id', '!=', $ignoreNewsId);
            }

            if (! $candidateQuery->exists()) {
                return $candidate;
            }

            $suffix++;
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

    /**
     * @OA\Get(
     *     path="/my-dashboard-stat",
     *     tags={"Reporter"},
     *     summary="Dashboard Statistics",
     *     description="Get dashboard statistics for the authenticated reporter.",
     *     operationId="dashboardStat",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard statistics fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Dashboard statistics fetched successfully"
     *             ),
     *
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *
     *                 @OA\Property(
     *                     property="total_news",
     *                     type="integer",
     *                     example=10
     *                 ),
     *
     *                 @OA\Property(
     *                     property="total_pending_news",
     *                     type="integer",
     *                     example=5
     *                 ),
     *
     *                 @OA\Property(
     *                     property="total_rejected_news",
     *                     type="integer",
     *                     example=2
     *                 ),
     *
     *                 @OA\Property(
     *                     property="total_approved_news",
     *                     type="integer",
     *                     example=3
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
     *         response=403,
     *         description="Forbidden",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Forbidden."
     *             )
     *         )
     *     )
     * )
     */
    public function dashboardStat()
    {
        try {

            $language = Auth::user()?->language ?? 'eng';

            $total_news = News::where('user_id', Auth::id())->count();

            $total_pending_news = News::where('user_id', Auth::id())
                ->where('status', 'pending')
                ->count();

            $total_rejected_news = News::where('user_id', Auth::id())
                ->where('status', 'rejected')
                ->count();

            $total_approved_news = News::where('user_id', Auth::id())
                ->where('status', 'approved')
                ->count();

            $data = [
                'total_news' => $total_news,
                'total_pending_news' => $total_pending_news,
                'total_rejected_news' => $total_rejected_news,
                'total_approved_news' => $total_approved_news,
            ];

            $message = match ($language) {
                'hin' => 'डैशबोर्ड के आँकड़े सफलतापूर्वक प्राप्त कर लिए गए।',
                'guj' => 'ડેશબોર્ડ આંકડા સફળતાપૂર્વક મેળવ્યા.',
                default => 'Dashboard statistics fetched successfully',
            };

            return Util::getSuccessMessage(
                $message,
                $data
            );
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/media",
     *     summary="Get All Media",
     *     description="Retrieve a list of all media records.",
     *     operationId="getAllMedia",
     *     tags={"Media"},
     *     security={{"bearerAuth":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Media list fetched successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="status",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Media list fetched successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(
     *                         property="id",
     *                         type="integer",
     *                         example=1
     *                     ),
     *                     @OA\Property(
     *                         property="media_url",
     *                         type="string",
     *                         example="https://example.com/uploads/media/image.jpg"
     *                     ),
     *                     @OA\Property(
     *                         property="media_type",
     *                         type="string",
     *                         example="image"
     *                     ),
     *                     @OA\Property(
     *                         property="created_at",
     *                         type="string",
     *                         format="date-time"
     *                     ),
     *                     @OA\Property(
     *                         property="updated_at",
     *                         type="string",
     *                         format="date-time"
     *                     )
     *                 )
     *             )
     *         )
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
    public function getAllMedia()
    {
        try {
            $language = Auth::user()?->language ?? 'guj';
            $message = match ($language) {
                'hin' => 'मीडिया सूची सफलतापूर्वक प्राप्त की गई',
                'guj' => 'મીડિયા સૂચિ સફળતાપૂર્વક મેળવી',
                default => 'Media list fetched successfully',
            };
            $media = Media::all();
            return Util::getSuccessMessage(
                $message,
                $media
            );
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }
}
