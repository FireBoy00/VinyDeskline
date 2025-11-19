<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\DeskController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'showLogin'])->name('login');
Route::post('/login', [HomeController::class, 'login'])->name('login.submit');
Route::post('/personalize', [HomeController::class, 'savePersonalization'])->name('personalize.submit');
Route::get('/personalize', [HomeController::class, 'personalize'])->name('personalize');

Route::get('/home', [HomeController::class, 'index'])->name('home');
Route::get('/admin', [HomeController::class, 'admin'])->name('admin');

//desks
Route::get('/desks',[DeskController::class, 'index'])->name('desks');
Route::get('/desks/{desk_id}',[DeskController::class, 'state']);
