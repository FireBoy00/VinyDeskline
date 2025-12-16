<?php
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DeskController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OfficeManagementController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ScheduleController;

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
    Route::get('/no-desk', function() {
        return view('no-desk-assigned');
    })->name('no-desk');
    Route::get('/settings', [HomeController::class, 'settings'])->name('settings');
    Route::post('/settings/update-info', [HomeController::class, 'updateUserInfo'])->name('settings.update-info');
    Route::post('/settings/update-settings', [HomeController::class, 'updateUserSettings'])->name('settings.update-settings');
    Route::post('/settings/reset-data', [HomeController::class, 'resetUserData'])->name('settings.reset-data');
    Route::put('/desks/{desk_id}/set-height', [DeskController::class, 'set_height'])->name('desk.set-height');
    Route::put('/home/{deskId}/{positionIndex}/updateCustom', [HomeController::class, 'updateCustom']);
    Route::get('/about', [HomeController::class, 'about'])->name('about');
    Route::get('/help', [HomeController::class, 'help'])->name('help');
    
    // Admin-only routes
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/schedules', [AdminController::class, 'schedules'])->name('schedules');
        Route::post('/schedules', [ScheduleController::class, 'store']);
        Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');
        Route::get('/arrangement', [AdminController::class, 'arrangement'])->name('arrangement');
        Route::get('/user-management', [AdminController::class, 'userManagement'])->name('user-management');
        Route::get('/office-management', [OfficeManagementController::class, 'index'])->name('office-management');
        Route::get('/account', [AdminController::class, 'account'])->name('account');
        Route::get('/next-schedules', [AdminController::class, 'nextSchedules']);



        // Account management routes
        Route::post('/account/update-info', [AdminController::class, 'updateUserInfo'])->name('account.update-info');
        Route::post('/account/update-settings', [AdminController::class, 'updateUserSettings'])->name('account.update-settings');
        Route::post('/account/reset-settings', [AdminController::class, 'resetUserSettings'])->name('account.reset-settings');
        
        // Admin desk management
        Route::get('/desks', [DeskController::class, 'index'])->name('desks');
        Route::get('/desks/stats', [DeskController::class, 'stats'])->name('desks.stats');
        Route::get('/desks/{deskId}', [DeskController::class, 'show'])->name('desks.show');
        Route::put('/desks/{deskId}/height', [DeskController::class, 'setHeight'])->name('desks.set-height');
        Route::post('/desks/{deskId}/assign', [DeskController::class, 'assignUser'])->name('desks.assign-user');
        Route::post('/desks/{deskId}/unassign', [DeskController::class, 'unassignUser'])->name('desks.unassign-user');
        Route::get('/desks/{deskId}/metrics', [DeskController::class, 'getMetrics'])->name('desks.metrics');
    });
    
    // API routes for user management (admin only)
    Route::middleware('admin')->prefix('api')->name('api.')->group(function () {
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::post('/users/{userId}/assign-desk', [UserController::class, 'assignDesk'])->name('users.assign-desk');
        Route::post('/users/{userId}/unassign-desk', [UserController::class, 'unassignDesk'])->name('users.unassign-desk');
        
        // Office management API routes
        Route::get('/floors', [OfficeManagementController::class, 'getFloors'])->name('floors.index');
        Route::post('/floors', [OfficeManagementController::class, 'createFloor'])->name('floors.store');
        Route::put('/floors/{id}', [OfficeManagementController::class, 'updateFloor'])->name('floors.update');
        Route::delete('/floors/{id}', [OfficeManagementController::class, 'deleteFloor'])->name('floors.destroy');
        
        Route::get('/rooms', [OfficeManagementController::class, 'getRooms'])->name('rooms.index');
        Route::post('/rooms', [OfficeManagementController::class, 'createRoom'])->name('rooms.store');
        Route::put('/rooms/{id}', [OfficeManagementController::class, 'updateRoom'])->name('rooms.update');
        Route::delete('/rooms/{id}', [OfficeManagementController::class, 'deleteRoom'])->name('rooms.destroy');
        
        Route::put('/desks/{deskId}/location', [OfficeManagementController::class, 'assignDeskLocation'])->name('desks.assign-location');
    });
    
    // Logout (support both GET and POST for simplicity)
    Route::match(['get', 'post'], '/logout', [AuthController::class, 'logout'])->name('logout');
});
