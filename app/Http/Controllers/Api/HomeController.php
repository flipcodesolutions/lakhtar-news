<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use App\Models\Category;
use App\Models\News;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * @OA\Get(
     *     path="/categories",
     *     tags={"Categories"},
     *     summary="Get Categories",
     *     description="Retrieve all categories with optional search filter",
     *     operationId="getCategories",
     *     security={{"sanctum": {}}},
     *
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         description="Search category by name",
     *         required=false,
     *         @OA\Schema(
     *             type="string",
     *             example="Politics"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Categories retrieved successfully",
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
     *                 example="Categories retrieved successfully"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="categories",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Sports"),
     *                         @OA\Property(property="slug", type="string", example="sports"),
     *                         @OA\Property(property="image", type="string", example="uploads/cms/sports.jpg"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function getCategories(Request $request)
    {
        $search = $request->input('search', '');

        $language = Auth::user()?->language ?? 'guj';
        $column = match ($language) {
            'hin' => 'nameInHindi',
            'guj' => 'nameInGujarati',
            default => 'name',
        };

        $messages = [
            'eng' => 'Categories fetched successfully.',
            'hin' => 'श्रेणियां सफलतापूर्वक प्राप्त की गईं।',
            'guj' => 'કેટેગરી સફળતાપૂર્વક મેળવવામાં આવી છે.',
        ];


        $categories = Category::select(
            'id',
            "$column as name",
            'slug',
            'image',
            'created_at',
            'updated_at',
        )
            ->when($search, function ($query) use ($search, $column) {
                $query->where($column, 'like', "%{$search}%");
            })
            ->orderBy('id', 'asc')
            ->get();

        return Util::getSuccessMessage(
            $messages[$language] ?? $messages['eng'],
            [
                'categories' => $categories
            ]
        );
    }

    // breaking news news

    /**
     * @OA\Get(
     *     path="/get-breaking-news",
     *     tags={"Home"},
     *     summary="Get Breaking News",
     *     description="Fetch all breaking news based on the authenticated user's language preference.",
     *     operationId="getBreakingNews",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Breaking news fetched successfully",
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
     *                 example="Breaking news fetched successfully."
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
     *                         @OA\Property(property="category_id", type="integer", example=2),
     *                         @OA\Property(property="user_id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Breaking News Title"),
     *                         @OA\Property(property="slug", type="string", example="breaking-news-title"),
     *                         @OA\Property(property="description", type="string", example="Breaking news description."),
     *                         @OA\Property(property="image", type="string", example="news/1718100000_abcd1234.webp"),
     *                         @OA\Property(property="video", type="string", nullable=true, example="https://www.youtube.com/watch?v=dQw4w9WgXcQ"),
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
     *                         @OA\Property(property="news_type", type="string", example="breaking"),
     *                         @OA\Property(property="is_featured", type="integer", example=1),
     *                         @OA\Property(property="total_views", type="integer", example=250),
     *                         @OA\Property(property="publish_date", type="string", format="date", example="2026-06-05"),
     *                         @OA\Property(property="status", type="string", example="active"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time"),
     *
     *                         @OA\Property(
     *                             property="category",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=2),
     *                             @OA\Property(property="name", type="string", example="Politics")
     *                         ),
     *
     *                         @OA\Property(
     *                             property="user",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Admin")
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
     *     )
     * )
     */
    public function getBreakingNews(Request $request)
    {
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

        $messages = [
            'eng' => 'Breaking news fetched successfully.',
            'hin' => 'ब्रेकिंग न्यूज़ सफलतापूर्वक प्राप्त की गई।',
            'guj' => 'બ્રેકિંગ ન્યૂઝ સફળતાપૂર્વક મેળવવામાં આવી.',
        ];

        $news = News::with(['category', 'user', 'media'])
            ->where('news_type', 'breaking')
            ->latest()
            ->get()
            ->map(function ($item) use ($titleColumn, $descriptionColumn) {
                return [
                    'id' => $item->id,
                    'category_id' => $item->category_id,
                    'user_id' => $item->user_id,
                    'title' => $item->$titleColumn,
                    'slug' => $item->slug,
                    'description' => $item->$descriptionColumn,
                    'image' => $item->image,
                    'video' => $item->video,
                    'media' => $item->media->map(function ($mediaItem) {
                        return [
                            'id' => $mediaItem->id,
                            'media_type' => $mediaItem->media_type,
                            'file_path' => $mediaItem->file_path,
                            'thumbnail' => $mediaItem->thumbnail,
                            'caption' => $mediaItem->caption,
                            'sort_order' => $mediaItem->pivot?->sort_order,
                        ];
                    })->values(),
                    'news_type' => $item->news_type,
                    'is_featured' => $item->is_featured,
                    'total_views' => $item->total_views,
                    'publish_date' => $item->publish_date,
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'category' => $item->category,
                    'user' => $item->user,
                ];
            });

        return Util::getSuccessMessage(
            $messages[$language] ?? $messages['eng'],
            [
                'news' => $news
            ]
        );
    }

    // trending news news
    /**
     * @OA\Get(
     *     path="/get-trending-news",
     *     tags={"Home"},
     *     summary="Get Trending News",
     *     description="Fetch all trending news based on the authenticated user's language preference.",
     *     operationId="getTrendingNews",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Trending news fetched successfully",
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
     *                 example="Breaking news fetched successfully."
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
     *                         @OA\Property(property="category_id", type="integer", example=2),
     *                         @OA\Property(property="user_id", type="integer", example=1),
     *                         @OA\Property(property="title", type="string", example="Breaking News Title"),
     *                         @OA\Property(property="slug", type="string", example="breaking-news-title"),
     *                         @OA\Property(property="description", type="string", example="Breaking news description."),
     *                         @OA\Property(property="image", type="string", example="news/1718100000_abcd1234.webp"),
     *                         @OA\Property(property="video", type="string", nullable=true, example="https://www.youtube.com/watch?v=dQw4w9WgXcQ"),
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
     *                         @OA\Property(property="news_type", type="string", example="breaking"),
     *                         @OA\Property(property="is_featured", type="integer", example=1),
     *                         @OA\Property(property="total_views", type="integer", example=250),
     *                         @OA\Property(property="publish_date", type="string", format="date", example="2026-06-05"),
     *                         @OA\Property(property="status", type="string", example="active"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time"),
     *
     *                         @OA\Property(
     *                             property="category",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=2),
     *                             @OA\Property(property="name", type="string", example="Politics")
     *                         ),
     *
     *                         @OA\Property(
     *                             property="user",
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="Admin")
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
     *     )
     * )
     */
    public function getTrendingNews(Request $request)
    {
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

        $messages = [
            'eng' => 'Trending news fetched successfully.',
            'hin' => 'ट्रेंडिंग न्यूज़ सफलतापूर्वक प्राप्त की गई।',
            'guj' => 'ટ્રેન્ડિંગ ન્યૂઝ સફળતાપૂર્વક મેળવવામાં આવી.',
        ];

        $news = News::with(['category', 'user', 'media'])
            ->where('total_views', '>', 100)
            ->latest()
            ->get()
            ->map(function ($item) use ($titleColumn, $descriptionColumn) {
                return [
                    'id' => $item->id,
                    'category_id' => $item->category_id,
                    'user_id' => $item->user_id,
                    'title' => $item->$titleColumn,
                    'slug' => $item->slug,
                    'description' => $item->$descriptionColumn,
                    'image' => $item->image,
                    'video' => $item->video,
                    'media' => $item->media->map(function ($mediaItem) {
                        return [
                            'id' => $mediaItem->id,
                            'media_type' => $mediaItem->media_type,
                            'file_path' => $mediaItem->file_path,
                            'thumbnail' => $mediaItem->thumbnail,
                            'caption' => $mediaItem->caption,
                            'sort_order' => $mediaItem->pivot?->sort_order,
                        ];
                    })->values(),
                    'news_type' => $item->news_type,
                    'is_featured' => $item->is_featured,
                    'total_views' => $item->total_views,
                    'publish_date' => $item->publish_date,
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'category' => $item->category,
                    'user' => $item->user,
                ];
            });

        return Util::getSuccessMessage(
            $messages[$language] ?? $messages['eng'],
            [
                'news' => $news
            ]
        );
    }

    /**
     * @OA\Get(
     *     path="/news-details/{id}",
     *     tags={"Home"},
     *     summary="Get News Details",
     *     description="Fetch news details by ID based on the authenticated user's language preference.",
     *     operationId="getNewsDetails",
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
     *         description="News details fetched successfully.",
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
     *                 example="News details fetched successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="news",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="title", type="string", example="Breaking News Title"),
     *                     @OA\Property(property="description", type="string", example="This is the news description."),
     *                     @OA\Property(property="image", type="string", example="news/1718100000_abcd1234.webp"),
     *                     @OA\Property(property="video", type="string", nullable=true, example="https://www.youtube.com/watch?v=dQw4w9WgXcQ"),
     *                     @OA\Property(
     *                         property="media",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=10),
     *                             @OA\Property(property="media_type", type="string", example="image"),
     *                             @OA\Property(property="file_path", type="string", example="news/1718100000_abcd1234.webp"),
     *                             @OA\Property(property="thumbnail", type="string", nullable=true, example=null),
     *                             @OA\Property(property="caption", type="string", nullable=true, example=null),
     *                             @OA\Property(property="sort_order", type="integer", example=0)
     *                         )
     *                     ),
     *                     @OA\Property(property="news_type", type="string", example="breaking"),
     *                     @OA\Property(
     *                         property="created_at",
     *                         type="string",
     *                         format="date-time",
     *                         example="2026-06-06T10:00:00.000000Z"
     *                     ),
     *
     *                     @OA\Property(
     *                         property="category",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=2),
     *                         @OA\Property(property="name", type="string", example="Politics")
     *                     ),
     *
     *                     @OA\Property(
     *                         property="user",
     *                         type="object",
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="name", type="string", example="Admin")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="News not found.",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="News not found.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     )
     * )
     */
    public function getNewsDetails(Request $request, $id)
    {
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

        $categoryColumn = match ($language) {
            'hin' => 'nameInHindi',
            'guj' => 'nameInGujarati',
            default => 'name',
        };

        $message = match ($language) {
            'hin' => 'समाचार विवरण सफलतापूर्वक प्राप्त कर लिया गया।',
            'guj' => 'સમાચાર વિગતો સફળતાપૂર્વક મેળવી.',
            default => 'News details fetched successfully.',
        };

        $news = News::with([
            'category:id,' . $categoryColumn,
            'user:id,name',
            'media',
        ])->find($id);

        if (!$news) {
            return Util::getErrorMessage('News not found.');
        }

        $media = $news->media->map(function ($mediaItem) {
            return [
                'id' => $mediaItem->id,
                'media_type' => $mediaItem->media_type,
                'file_path' => $mediaItem->file_path,
                'thumbnail' => $mediaItem->thumbnail,
                'caption' => $mediaItem->caption,
                'sort_order' => $mediaItem->pivot?->sort_order,
            ];
        })->values();

        $response = [
            'id' => $news->id,
            'title' => $news->$titleColumn,
            'slug' => $news->slug,
            'description' => $news->$descriptionColumn,
            'image' => $news->image,
            'video' => $news->video,
            'media' => $media,
            'news_type' => $news->news_type,
            'is_featured' => $news->is_featured,
            'total_views' => $news->total_views,
            'publish_date' => $news->publish_date,
            'status' => $news->status,
            'created_at' => $news->created_at,
            'category' => [
                'id' => $news->category?->id,
                'name' => $news->category?->$categoryColumn,
            ],
            'user' => $news->user,
        ];

        return Util::getSuccessMessage(
            $message,
            ['news' => $response]
        );
    }


    /**
     * @OA\Get(
     *     path="/video-news",
     *     tags={"News"},
     *     summary="Get Video News",
     *     description="Fetch all news records that contain a video. Title and description are returned according to the authenticated user's selected language.",
     *     operationId="getVideoNews",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Response(
     *         response=200,
     *         description="Video news fetched successfully.",
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
     *                 example="Video news fetched successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="news",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(
     *                             property="id",
     *                             type="integer",
     *                             example=1
     *                         ),
     *                         @OA\Property(
     *                             property="title",
     *                             type="string",
     *                             example="Breaking News Video"
     *                         ),
     *                         @OA\Property(
     *                             property="description",
     *                             type="string",
     *                             example="This is the video news description."
     *                         ),
     *                         @OA\Property(property="image", type="string", example="news/1718100000_abcd1234.webp"),
     *                         @OA\Property(property="video", type="string", example="https://www.youtube.com/watch?v=dQw4w9WgXcQ"),
     *                         @OA\Property(
     *                             property="media",
     *                             type="array",
     *                             @OA\Items(
     *                                 type="object",
     *                                 @OA\Property(property="id", type="integer", example=10),
     *                                 @OA\Property(property="media_type", type="string", example="video"),
     *                                 @OA\Property(property="file_path", type="string", example="https://www.youtube.com/watch?v=dQw4w9WgXcQ"),
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
     *                             property="created_at",
     *                             type="string",
     *                             format="date-time",
     *                             example="2026-06-08T10:30:00.000000Z"
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
    public function getVideoNews(Request $request)
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
                'hin' => 'वीडियो समाचार सफलतापूर्वक प्राप्त हो गया।',
                'guj' => 'વિડિઓ સમાચાર સફળતાપૂર્વક મેળવ્યા.',
                default => 'Video news fetched successfully.',
            };

            $news = News::with('media')
                ->whereHas('media', fn($query) => $query->where('media_type', 'video'))
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($item) use ($titleColumn, $descriptionColumn) {
                    return [
                        'id' => $item->id,
                        'title' => $item->$titleColumn,
                        'slug' => $item->slug,
                        'description' => $item->$descriptionColumn,
                        'image' => $item->image,
                        'video' => $item->video,
                        'media' => $item->media->map(function ($mediaItem) {
                            return [
                                'id' => $mediaItem->id,
                                'media_type' => $mediaItem->media_type,
                                'file_path' => $mediaItem->file_path,
                                'thumbnail' => $mediaItem->thumbnail,
                                'caption' => $mediaItem->caption,
                                'sort_order' => $mediaItem->pivot?->sort_order,
                            ];
                        })->values(),
                        'total_views' => $item->total_views,
                        'publish_date' => $item->publish_date,
                        'news_type' => $item->news_type,
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
     *     path="/get-news/{slug}",
     *     tags={"News"},
     *     summary="Get News By Slug",
     *     description="Fetch news details by slug according to the authenticated user's selected language.",
     *     operationId="getNewsBySlug",
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="slug",
     *         in="path",
     *         required=true,
     *         description="News slug",
     *         @OA\Schema(
     *             type="string",
     *             example="breaking-news-in-gujarat"
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="News details fetched successfully.",
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
     *                 example="News details fetched successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="news",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(
     *                         property="title",
     *                         type="string",
     *                         example="Breaking News in Gujarat"
     *                     ),
     *                     @OA\Property(
     *                         property="description",
     *                         type="string",
     *                         example="This is the detailed news description."
     *                     ),
     *                     @OA\Property(
     *                         property="slug",
     *                         type="string",
     *                         example="breaking-news-in-gujarat"
     *                     ),
     *                     @OA\Property(property="image", type="string", example="news/1718100000_abcd1234.webp"),
     *                     @OA\Property(
     *                         property="video",
     *                         type="string",
     *                         nullable=true,
     *                         example="https://www.youtube.com/watch?v=dQw4w9WgXcQ"
     *                     ),
     *                     @OA\Property(
     *                         property="media",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=10),
     *                             @OA\Property(property="media_type", type="string", example="image"),
     *                             @OA\Property(property="file_path", type="string", example="news/1718100000_abcd1234.webp"),
     *                             @OA\Property(property="thumbnail", type="string", nullable=true, example=null),
     *                             @OA\Property(property="caption", type="string", nullable=true, example=null),
     *                             @OA\Property(property="sort_order", type="integer", example=0)
     *                         )
     *                     ),
     *                     @OA\Property(
     *                         property="news_type",
     *                         type="string",
     *                         example="breaking"
     *                     ),
     *                     @OA\Property(
     *                         property="created_at",
     *                         type="string",
     *                         format="date-time",
     *                         example="2026-06-08T10:30:00.000000Z"
     *                     ),
     *
     *                     @OA\Property(
     *                         property="category",
     *                         type="object",
     *                         @OA\Property(
     *                             property="id",
     *                             type="integer",
     *                             example=5
     *                         ),
     *                         @OA\Property(
     *                             property="name",
     *                             type="string",
     *                             example="Politics"
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="News not found.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=false
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="News not found."
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

    public function getNewsBySlug(Request $request, $slug)
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

            $categoryColumn = match ($language) {
                'hin' => 'nameInHindi',
                'guj' => 'nameInGujarati',
                default => 'name',
            };

            $message = match ($language) {
                'hin' => 'समाचार विवरण सफलतापूर्वक प्राप्त कर लिया गया।',
                'guj' => 'સમાચાર વિગતો સફળતાપૂર્વક મેળવી.',
                default => 'News details fetched successfully.',
            };

            $news = News::with(['category', 'media'])
                ->where('slug', $slug)
                ->first();

            if (!$news) {
                return Util::getErrorMessage(
                    'News not found.'
                );
            }

            $response = [
                'id' => $news->id,
                'title' => $news->$titleColumn,
                'description' => $news->$descriptionColumn,
                'slug' => $news->slug,
                'image' => $news->image,
                'video' => $news->video,
                'media' => $news->media->map(function ($mediaItem) {
                    return [
                        'id' => $mediaItem->id,
                        'media_type' => $mediaItem->media_type,
                        'file_path' => $mediaItem->file_path,
                        'thumbnail' => $mediaItem->thumbnail,
                        'caption' => $mediaItem->caption,
                        'sort_order' => $mediaItem->pivot?->sort_order,
                    ];
                })->values(),
                'news_type' => $news->news_type,
                'created_at' => $news->created_at,

                'category' => [
                    'id' => $news->category?->id,
                    'name' => $news->category?->$categoryColumn,
                ],
            ];

            return Util::getSuccessMessage(
                $message,
                [
                    'news' => $response
                ]
            );
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    /**
     * @OA\Get(
 *     path="/get-category-news/{id}",
     *     summary="Get category news",
     *     description="Fetch category details along with all associated news and media based on the authenticated user's selected language.",
     *     tags={"News"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Category ID",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Category news fetched successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean",
     *                 example=true
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Category news fetched successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="category",
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="Sports"),
     *                     @OA\Property(property="description", type="string", example="Latest sports news"),
     *                     @OA\Property(
     *                         property="news",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(property="id", type="integer", example=10),
     *                             @OA\Property(property="slug", type="string", example="india-wins-final"),
     *                             @OA\Property(property="title", type="string", example="India Wins Final"),
     *                             @OA\Property(property="description", type="string", example="India secured a historic victory."),
     *                             @OA\Property(property="image", type="string", example="uploads/news/image.jpg"),
     *                             @OA\Property(property="video", type="string", nullable=true, example="uploads/news/video.mp4"),
     *                             @OA\Property(
     *                                 property="media",
     *                                 type="array",
     *                                 @OA\Items(
     *                                     type="object",
     *                                     @OA\Property(property="id", type="integer", example=1),
     *                                     @OA\Property(property="media_type", type="string", example="image"),
     *                                     @OA\Property(property="file_path", type="string", example="uploads/media/image.jpg"),
     *                                     @OA\Property(property="thumbnail", type="string", nullable=true, example="uploads/media/thumb.jpg"),
     *                                     @OA\Property(property="caption", type="string", nullable=true, example="Match Celebration"),
     *                                     @OA\Property(property="sort_order", type="integer", nullable=true, example=1)
     *                                 )
     *                             ),
     *                             @OA\Property(property="publish_date", type="string", format="date", example="2026-06-25"),
     *                             @OA\Property(property="news_type", type="string", example="breaking"),
     *                             @OA\Property(property="created_at", type="string", format="date-time", example="2026-06-25T10:30:00Z")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Category not found.")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Something went wrong.")
     *         )
     *     )
     * )
     */
    public function getCategoryNews(Request $request, $id)
    {
        try {
            $language = Auth::user()?->language ?? 'eng';

            $categoryColumn = match ($language) {
                'hin' => 'nameInHindi',
                'guj' => 'nameInGujarati',
                default => 'name',
            };

            $newsColumn = match ($language) {
                'hin' => 'titleInHindi',
                'guj' => 'titleInGujarati',
                default => 'title',
            };

            $categoryDescriptionColumn = match ($language) {
                'hin' => 'descriptionInHindi',
                'guj' => 'descriptionInGujarati',
                default => 'description',
            };
            $newsDescriptionColumn = match ($language) {
                'hin' => 'descriptionInHindi',
                'guj' => 'descriptionInGujarati',
                default => 'description',
            };


            $message = match ($language) {
                'hin' => 'समाचार विवरण सफलतापूर्वक प्राप्त कर लिया गया।',
                'guj' => 'સમાચાર વિગતો સફળતાપૂર્વક મેળવી.',
                default => 'Category news fetched successfully.',
            };

            $category = Category::with(['news' => function ($query) {
                $query->with('media')->orderByDesc('id');
            }])->where('id', $id)->first();

            if (!$category) {
                return Util::getErrorMessage(
                    'Category not found.'
                );
            }

            $newsList = $category->news->map(function ($newsItem) use ($newsColumn, $newsDescriptionColumn) {
                return [
                    'id' => $newsItem->id,
                    'slug' => $newsItem->slug,
                    'title' => $newsItem->$newsColumn,
                    'description' => $newsItem->$newsDescriptionColumn,
                    'image' => $newsItem->image,
                    'video' => $newsItem->video,
                    'media' => $newsItem->media->map(function ($mediaItem) {
                        return [
                            'id' => $mediaItem->id,
                            'media_type' => $mediaItem->media_type,
                            'file_path' => $mediaItem->file_path,
                            'thumbnail' => $mediaItem->thumbnail,
                            'caption' => $mediaItem->caption,
                            'sort_order' => $mediaItem->pivot?->sort_order,
                        ];
                    })->values(),
                    'publish_date' => $newsItem->publish_date,
                    'news_type' => $newsItem->news_type,
                    'created_at' => $newsItem->created_at,
                ];
            })->values();

            $response = [
                'id' => $category->id,
                'name' => $category->$categoryColumn,
                'description' => $category->$categoryDescriptionColumn,
                'news' => $newsList,
            ];

            return Util::getSuccessMessage(
                $message,
                [
                    'category' => $response
                ]
            );
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/banners",
     *     tags={"Banner"},
     *     summary="Get Active Banners",
     *     description="Fetch all active banners whose start date and end date are within the current date range.",
     *     operationId="getBanners",
     *
     *     @OA\Response(
     *         response=200,
     *         description="Banners fetched successfully.",
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
     *                 example="Banners fetched successfully."
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="banners",
     *                     type="array",
     *                     @OA\Items(
     *                         type="object",
     *                         @OA\Property(
     *                             property="id",
     *                             type="integer",
     *                             example=1
     *                         ),
     *                         @OA\Property(
     *                             property="title",
     *                             type="string",
     *                             example="Breaking News Banner"
     *                         ),
     *                         @OA\Property(
     *                             property="image",
     *                             type="string",
     *                             example="banners/banner-1.jpg"
     *                         ),
     *                         @OA\Property(
     *                             property="link",
     *                             type="string",
     *                             nullable=true,
     *                             example="https://example.com"
     *                         ),
     *                         @OA\Property(
     *                             property="start_date",
     *                             type="string",
     *                             format="date",
     *                             example="2026-06-01"
     *                         ),
     *                         @OA\Property(
     *                             property="end_date",
     *                             type="string",
     *                             format="date",
     *                             example="2026-06-30"
     *                         ),
     *                         @OA\Property(
     *                             property="is_active",
     *                             type="boolean",
     *                             example=true
     *                         ),
     *                         @OA\Property(
     *                             property="created_at",
     *                             type="string",
     *                             format="date-time"
     *                         ),
     *                         @OA\Property(
     *                             property="updated_at",
     *                             type="string",
     *                             format="date-time"
     *                         )
     *                     )
     *                 )
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

    public function getBanners()
    {
        try {
            $banners = Banner::query()
                ->where('status', true)
                ->whereDate('start_date', '<=', now())
                ->whereDate('end_date', '>=', now())
                ->orderByDesc('id')
                ->get();

            return Util::getSuccessMessage(
                'Banners fetched successfully.',
                [
                    'banners' => $banners
                ]
            );
        } catch (\Exception $e) {
            return Util::getErrorMessage($e->getMessage());
        }
    }
}
