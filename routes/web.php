<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DeskController;
use Illuminate\Support\Facades\Route;

// Guest routes (only accessible when not logged in)
Route::middleware('guest')->group(function () {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    // Personalization routes
    Route::get('/personalize', [AuthController::class, 'showPersonalize'])->name('personalize');
    Route::post('/personalize', [AuthController::class, 'savePersonalization'])->name('personalize.submit');
    Route::get('/personalize/skip', [AuthController::class, 'skipPersonalization'])->name('personalize.skip');
    
    // Main application routes (all authenticated users)
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    
    // Admin-only routes
    Route::middleware('admin')->group(function () {
        Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
        
        // Admin desk management
        Route::get('/desks', [DeskController::class, 'index'])->name('desks');
        Route::get('/desks/{desk_id}', [DeskController::class, 'state']);
    });
    
    // Logout (support both GET and POST for simplicity)
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');
});


