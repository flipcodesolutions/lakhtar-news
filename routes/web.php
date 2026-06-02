<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\HomeController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('admin.dashboard')
        : redirect()->route('admin.login');
});

Route::get('/login', [AuthController::class, 'login'])->name('admin.login');
Route::post('/login', [AuthController::class, 'loginPost'])->name('admin.login.post');


Route::prefix('admin')->group(function () {
    // dashboard routes
    Route::get('/dashboard', [HomeController::class, 'dashboard'])->name('admin.dashboard');

    // user routes
    Route::get('/users', [UserController::class, 'index'])->name('admin.user.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('admin.user.create');
    Route::post('/users', [UserController::class, 'store'])->name('admin.user.store');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('admin.user.show');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('admin.user.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('admin.user.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('admin.user.destroy');

    // language routes
    Route::get('/languages', [LanguageController::class, 'index'])->name('admin.language.index');
    Route::get('/languages/create', [LanguageController::class, 'create'])->name('admin.language.create');
    Route::post('/languages/store', [LanguageController::class, 'store'])->name('admin.language.store');
    Route::get('/languages/{id}/edit', [LanguageController::class, 'edit'])->name('admin.language.edit');
    Route::put('/languages/{id}', [LanguageController::class, 'update'])->name('admin.language.update');
    Route::delete('/languages/{id}', [LanguageController::class, 'destroy'])->name('admin.language.destroy');

    // category routes
    Route::get('/category', [CategoryController::class, 'index'])->name('admin.category.index');
    Route::get('/category/create', [CategoryController::class, 'create'])->name('admin.category.create');
    Route::post('/category/store', [CategoryController::class, 'store'])->name('admin.category.store');
    Route::get('/category/edit/{id}', [CategoryController::class, 'edit'])->name('admin.category.edit');
    Route::put('/category/{id}', [CategoryController::class, 'update'])->name('admin.category.update');
    Route::delete('/category/{id}', [CategoryController::class, 'destroy'])->name('admin.category.destroy');
});
