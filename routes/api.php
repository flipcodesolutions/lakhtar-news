<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ReporterController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/send-otp', [AuthController::class, 'sendOTP']);
Route::post('/verify-otp', [AuthController::class, 'verifyOTP']);
Route::post('/register', [AuthController::class, 'register']);

Route::get('/categories', [HomeController::class, 'getCategories']);
Route::get('/banners', [HomeController::class, 'getBanners']);

Route::middleware('auth:sanctum')->group(function () {

    Route::get('/profile', [AuthController::class, 'getProfile']);
    Route::put('/update-profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-language', [AuthController::class, 'changeLanguage']);

    // home
    Route::get('/get-breaking-news', [HomeController::class, 'getBreakingNews']);
    Route::get('/get-trending-news', [HomeController::class, 'getTrendingNews']);
    Route::get('/news-details/{id}', [HomeController::class, 'getNewsDetails']);

    // CATEGORY WISE NEWS
    Route::get('/get-category-news/{id}', [HomeController::class, 'getCategoryNews']);


    // user
    Route::get('/my-interest', [AuthController::class, 'getMyInterest']);
    Route::put('/update-my-interest', [AuthController::class, 'updateMyInterest']);
    Route::get('/video-news', [HomeController::class, 'getVideoNews']);
    Route::get('/get-news/{slug}', [HomeController::class, 'getNewsBySlug']);

    // bookmark
    Route::get('/my-bookmarks', [AuthController::class, 'getMyBookmarks']);
    Route::post('/add-bookmark-news', [AuthController::class, 'addBookmarkNews']);
    Route::delete('/remove-bookmark/{id}', [AuthController::class, 'removeBookmarkNews']);

    // watch history
    Route::get('/watch-histories', [AuthController::class, 'getWatchHistories']);
    Route::post('/add-watch-history', [AuthController::class, 'addWatchHistory']);
    Route::delete('/remove-watch-history/{id}', [AuthController::class, 'removeWatchHistory']);
    Route::delete('/remove-all-watch-histories', [AuthController::class, 'removeAllWatchHistories']);


    // Reporter side's APIs
    Route::get('/my-news', [ReporterController::class, 'getMyNews']);
    Route::get('/my-news/{id}', [ReporterController::class, 'getMyNewsDetails']);
    Route::post('/create-news', [ReporterController::class, 'createNews']);
    Route::put('/update-news/{id}', [ReporterController::class, 'updateNews']);
    Route::delete('/delete-news/{id}', [ReporterController::class, 'deleteNews']);
    Route::get('/my-dashboard-stat', [ReporterController::class, 'dashboardStat']);

    Route::get('/media', [ReporterController::class, 'getAllMedia']);
    Route::get('/top-reporter', [HomeController::class, 'getTopReporters']);

    Route::get('/comments/{news_id}', [ReporterController::class, 'getComments']);
    Route::post('/add-comment', [ReporterController::class, 'addComment']);
    Route::delete('/delete-comment/{id}', [ReporterController::class, 'deleteComment']);
    Route::post('/report-comment/{id}', [ReporterController::class, 'reportComment']);

    Route::post('/like-news/{news_id}', [ReporterController::class, 'addLike']);
    Route::delete('/unlike-news/{news_id}', [ReporterController::class, 'removeLike']);

    Route::get('/alerts', [ReporterController::class, 'getAlerts']);

    Route::get('/my-liked-news', [HomeController::class, 'myLikedNews']);
});
// cms APIs
Route::get('/cms', [AuthController::class, 'getCmsSlugs']);
Route::get('/cms/{slug}', [AuthController::class, 'getCmsDetails']);
