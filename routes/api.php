<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/send-otp', [AuthController::class, 'sendOTP']);
Route::post('/verify-otp', [AuthController::class, 'verifyOTP']);
Route::post('/register', [AuthController::class, 'register']);


Route::middleware('auth:sanctum')->group(function () {

    Route::put('/update-profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-language', [AuthController::class, 'changeLanguage']);

    // home
    Route::get('/categories', [HomeController::class, 'getCategories']);
    Route::get('/get-breaking-news', [HomeController::class, 'getBreakingNews']);
    Route::get('/get-trending-news', [HomeController::class, 'getTrendingNews']);
    Route::get('/news-details/{id}', [HomeController::class, 'getNewsDetails']);


    // user
    Route::get('/my-interest', [AuthController::class, 'getMyInterest']);
    Route::put('/update-my-interest', [AuthController::class, 'updateMyInterest']);
});
