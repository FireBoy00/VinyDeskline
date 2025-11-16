<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'showLogin'])->name('login');
Route::post('/login', [HomeController::class, 'login'])->name('login.submit');
Route::post('/personalize', [HomeController::class, 'savePersonalization'])->name('personalize.submit');
Route::get('/personalize', [HomeController::class, 'personalize'])->name('personalize');

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/admin', [HomeController::class, 'admin'])->name('admin');
