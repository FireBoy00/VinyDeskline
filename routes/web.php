<?php
use App\Http\Controllers\DeskUserController;
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
    Route::get('/settings', [HomeController::class, 'settings'])->name('settings');
    Route::post('/settings/update-info', [HomeController::class, 'updateUserInfo'])->name('settings.update-info');
    Route::post('/settings/reset-data', [HomeController::class, 'resetUserData'])->name('settings.reset-data');
    
    // Admin-only routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/schedules', [AdminController::class, 'schedules'])->name('schedules');
        Route::get('/arrangement', [AdminController::class, 'arrangement'])->name('arrangement');
        Route::get('/account', [AdminController::class, 'account'])->name('account');
        Route::get('/user-management', [DeskUserController::class, 'index'])->name('user-management');
        Route::post('/user-management/store', [DeskUserController::class, 'store']);
        Route::delete('/user-management/{deskUser}', [DeskUserController::class, 'destroy']);


        
        // Account management routes
        Route::post('/account/update-info', [AdminController::class, 'updateUserInfo'])->name('account.update-info');
        Route::post('/account/update-settings', [AdminController::class, 'updateUserSettings'])->name('account.update-settings');
        Route::post('/account/reset-settings', [AdminController::class, 'resetUserSettings'])->name('account.reset-settings');
        
        // Admin desk management
        Route::get('/desks', [DeskController::class, 'index'])->name('desks');
        Route::get('/desks/stats', [DeskController::class, 'stats'])->name('desks.stats');
        Route::get('/desks/{desk_id}', [DeskController::class, 'state'])->name('desks.state');
    });
    
    // Logout (support both GET and POST for simplicity)
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');
});
