<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
     *                             example="uploads/news/news.jpg"
     *                         ),
     *                         @OA\Property(
     *                             property="video",
     *                             type="string",
     *                             nullable=true,
     *                             example="uploads/news/video.mp4"
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

            $news = News::where('user_id', Auth::id())
                ->orderBy('id', 'desc')
                ->get()
                ->map(function ($item) use ($titleColumn, $descriptionColumn) {
                    return [
                        'id' => $item->id,
                        'title' => $item->$titleColumn,
                        'description' => $item->$descriptionColumn,
                        'slug' => $item->slug,
                        'image' => $item->image,
                        'video' => $item->video,
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
     *     description="Create a new news article with multilingual content, image, video and video thumbnail.",
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
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="News image"
     *                 ),
     *                 @OA\Property(
     *                     property="video",
     *                     type="string",
     *                     format="url",
     *                     description="News video"
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
            $request->validate([
                'category_id' => 'required|integer|exists:categories,id',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'titleInHindi' => 'required|string|max:255',
                'descriptionInHindi' => 'required|string',
                'titleInGujarati' => 'required|string|max:255',
                'descriptionInGujarati' => 'required|string',
                'image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'video' => 'string',
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
            $news->video = $request->video;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('news'), $fileName);
                $news->image = 'news/' . $fileName;
            }
            $news->news_type = $request->news_type;
            $news->is_featured = $request->is_featured;
            $news->publish_date = $request->publish_date;
            $news->save();
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
     *     description="Update an existing news article.",
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
     *                     property="image",
     *                     type="string",
     *                     format="binary",
     *                     description="News image"
     *                 ),
     *                 @OA\Property(
     *                     property="video",
     *                     type="string",
     *                     example="https://example.com/video.mp4",
     *                     description="Video URL"
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

        $news = News::find($id);
        if (!$news) {
            return Util::getErrorMessage('News not found');
        }
        $language = Auth::user()?->language ?? 'eng';
        $message = match ($language) {
            'hin' => 'रिपोर्टर की खबर सफलतापूर्वक अपडेट हो गई।',
            'guj' => 'રિપોર્ટર સમાચાર સફળતાપૂર્વક અપડેટ થયા',
            default => 'Reporter news updated successfully',
        };
        try {

            $request->validate([
                'category_id' => 'required|integer|exists:categories,id',
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'titleInHindi' => 'required|string|max:255',
                'descriptionInHindi' => 'required|string',
                'titleInGujarati' => 'required|string|max:255',
                'descriptionInGujarati' => 'required|string',
                'image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
                'video' => 'string',
                'news_type' => 'required|string|in:normal,breaking,trending,live',
                'is_featured' => 'required|boolean',
                'publish_date' => 'required|date',
            ]);

            $news = News::find($id);
            if (!$news) {
                return Util::getErrorMessage('News not found');
            }
            $news->category_id = $request->category_id;
            $news->title = $request->title;
            $news->slug = Str::slug($request->title);
            $news->user_id = Auth::user()->id;
            $news->description = $request->description;
            $news->titleInHindi = $request->titleInHindi;
            $news->descriptionInHindi = $request->descriptionInHindi;
            $news->titleInGujarati = $request->titleInGujarati;
            $news->descriptionInGujarati = $request->descriptionInGujarati;
            $news->video = $request->video;
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $fileName = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('news'), $fileName);
                $news->image = 'news/' . $fileName;
            }
            $news->news_type = $request->news_type;
            $news->is_featured = $request->is_featured;
            $news->publish_date = $request->publish_date;
            $news->save();
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

        $news = News::where('id', $id)->where('user_id', $userId)->first();
        if (!$news) {
            return Util::getErrorMessage('News not found');
        }
        $news->delete();
        return Util::getSuccessMessage($message);
    }
}
