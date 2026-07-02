<?php

use App\Http\Controllers\Admin\AlertController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\NewsController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('admin.login');
});

Route::get('/login', [AuthController::class, 'login'])->name('admin.login');
Route::post('/login', [AuthController::class, 'loginPost'])->name('admin.login.post');
Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('admin.password.request');
Route::post('/forgot-password', [AuthController::class, 'forgotPasswordPost'])->name('admin.password.email');
Route::get('/reset-password/{token}', [AuthController::class, 'resetPassword'])->name('admin.password.reset');
Route::post('/reset-password', [AuthController::class, 'resetPasswordPost'])->name('admin.password.store');


Route::post('/logout', [AuthController::class, 'logout'])->name('admin.logout')->middleware('auth');

Route::prefix('admin')->middleware('auth')->group(function () {
    // dashboard routes
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/password/change', [HomeController::class, 'passwordChange'])->name('admin.password.change');
    Route::post('/password/change', [HomeController::class, 'passwordUpdate'])->name('admin.password.update');
    Route::get('/profile', [HomeController::class, 'profile'])->name('admin.profile');
    Route::post('/profile', [HomeController::class, 'profileUpdate'])->name('admin.profile.update');

    // user routes
    Route::get('/users', [UserController::class, 'index'])->name('admin.user.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('admin.user.create');
    Route::post('/users', [UserController::class, 'store'])->name('admin.user.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('admin.user.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.user.update');
    Route::get('/users/{id}', [UserController::class, 'destroy'])->name('admin.user.destroy');

    // category routes
    Route::get('/category', [CategoryController::class, 'index'])->name('admin.category.index');
    Route::get('/category/create', [CategoryController::class, 'create'])->name('admin.category.create');
    Route::post('/category/store', [CategoryController::class, 'store'])->name('admin.category.store');
    Route::get('/category/edit/{id}', [CategoryController::class, 'edit'])->name('admin.category.edit');
    Route::put('/category/update', [CategoryController::class, 'update'])->name('admin.category.update');
    Route::get('/category/delete/{id}', [CategoryController::class, 'destroy'])->name('admin.category.destroy');

    // news routes
    Route::get('/news', [NewsController::class, 'index'])->name('admin.news.index');
    Route::get('/news/create', [NewsController::class, 'create'])->name('admin.news.create');
    Route::post('/news/store', [NewsController::class, 'store'])->name('admin.news.store');
    Route::get('/news/edit/{id}', [NewsController::class, 'edit'])->name('admin.news.edit');
    Route::put('/news/update', [NewsController::class, 'update'])->name('admin.news.update');
    Route::get('/news/delete/{id}', [NewsController::class, 'destroy'])->name('admin.news.destroy');

    // reporter news routes
    Route::get('/reporter-news', [NewsController::class, 'reporterIndex'])->name('admin.reporter-news.index');
    Route::get('/reporter-news/change-status/{id}/{status}', [NewsController::class, 'changeStatus'])->name('admin.reporter-news.change-status');

    // banner routes
    Route::get('/banners', [BannerController::class, 'index'])->name('admin.banner.index');
    Route::get('/banners/create', [BannerController::class, 'create'])->name('admin.banner.create');
    Route::post('/banners', [BannerController::class, 'store'])->name('admin.banner.store');
    Route::get('/banners/{id}/edit', [BannerController::class, 'edit'])->name('admin.banner.edit');
    Route::put('/banners/{id}', [BannerController::class, 'update'])->name('admin.banner.update');
    Route::get('/banners/{id}', [BannerController::class, 'destroy'])->name('admin.banner.destroy');

    // alert routes
    Route::get('/alerts', [AlertController::class, 'index'])->name('admin.alert.index');
    Route::get('/alerts/create', [AlertController::class, 'create'])->name('admin.alert.create');
    Route::post('/alerts', [AlertController::class, 'store'])->name('admin.alert.store');
    Route::get('/alerts/{id}/edit', [AlertController::class, 'edit'])->name('admin.alert.edit');
    Route::put('/alerts/{id}', [AlertController::class, 'update'])->name('admin.alert.update');
    Route::get('/alerts/{id}', [AlertController::class, 'destroy'])->name('admin.alert.destroy');

    Route::get('/media', [MediaController::class, 'index'])->name('admin.media.index');
    Route::get('/media/create', [MediaController::class, 'create'])->name('admin.media.create');
    Route::post('/media', [MediaController::class, 'store'])->name('admin.media.store');
    Route::get('/media/{id}/edit', [MediaController::class, 'edit'])->name('admin.media.edit');
    Route::put('/media/{id}', [MediaController::class, 'update'])->name('admin.media.update');
    Route::get('/media/{id}', [MediaController::class, 'destroy'])->name('admin.media.destroy');

    // top reporter routes
    Route::get('/top-reporters', [HomeController::class, 'topReporters'])->name('admin.top-reporters.index');
    Route::post('/top-reporters/store', [HomeController::class, 'storeTopReporter'])->name('admin.top-reporters.store');
    Route::get('/top-reporters/delete/{id}', [HomeController::class, 'destroyTopReporter'])->name('admin.top-reporters.destroy');

    // cms routes
    Route::get('/cms/{slug}', [CmsController::class, 'show'])->name('admin.cms.show');
    Route::put('/cms/update/{slug}', [CmsController::class, 'update'])->name('admin.cms.update');
});
