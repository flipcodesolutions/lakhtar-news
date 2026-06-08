<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        $language = Auth::user()?->language ?? 'en';
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
            $messages[$language] ?? $messages['en'],
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
     *                         @OA\Property(property="image", type="string", example="uploads/news/news.jpg"),
     *                         @OA\Property(property="video", type="string", nullable=true, example=null),
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
        $language = Auth::user()?->language ?? 'en';

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

        $news = News::where('news_type', 'breaking')
            ->select(
                'id',
                'category_id',
                'user_id',
                "$titleColumn as title",
                'slug',
                "$descriptionColumn as description",
                'image',
                'video',
                'news_type',
                'is_featured',
                'total_views',
                'publish_date',
                'status',
                'created_at',
                'updated_at'
            )
            ->with(['category', 'user'])
            ->latest()
            ->get();

        return Util::getSuccessMessage(
            $messages[$language] ?? $messages['en'],
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
     *                         @OA\Property(property="image", type="string", example="uploads/news/news.jpg"),
     *                         @OA\Property(property="video", type="string", nullable=true, example=null),
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
        $language = Auth::user()?->language ?? 'en';

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

        $news = News::where('total_views', '>', 100)
            ->select(
                'id',
                'category_id',
                'user_id',
                "$titleColumn as title",
                'slug',
                "$descriptionColumn as description",
                'image',
                'video',
                'news_type',
                'is_featured',
                'total_views',
                'publish_date',
                'status',
                'created_at',
                'updated_at'
            )
            ->with(['category', 'user'])
            ->latest()
            ->get();

        return Util::getSuccessMessage(
            $messages[$language] ?? $messages['en'],
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
     *                     @OA\Property(property="image", type="string", example="uploads/news/news.jpg"),
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
        $language = Auth::user()?->language ?? 'en';

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

        $news = News::with([
            'category:id,' . $categoryColumn,
            'user:id,name'
        ])->find($id);

        if (!$news) {
            return Util::getErrorMessage('News not found.');
        }

        $response = [
            'id' => $news->id,
            'title' => $news->$titleColumn,
            'slug' => $news->slug,
            'description' => $news->$descriptionColumn,
            'image' => $news->image,
            'video' => $news->video,
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
            'News details fetched successfully.',
            ['news' => $response]
        );
    }
}
