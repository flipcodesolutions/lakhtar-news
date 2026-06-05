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


Route::middleware('auth:sanctum')->group(function () {

    Route::put('/update-profile', [AuthController::class, 'updateProfile']);

    // home
    Route::get('/categories', [HomeController::class, 'getCategories']);
    Route::get('/get-breaking-news', [HomeController::class, 'getBreakingNews']);
    Route::get('/get-trending-news', [HomeController::class, 'getTrendingNews']);
});
