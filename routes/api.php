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



    // Reporter side's APIs
    Route::get('/my-news', [ReporterController::class, 'getMyNews']);
    Route::post('/create-news', [ReporterController::class, 'createNews']);
    Route::put('/update-news/{id}', [ReporterController::class, 'updateNews']);
    Route::delete('/delete-news/{id}', [ReporterController::class, 'deleteNews']);
});
