<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Nếu không cần auth, tạm thời dùng route public:
Route::get('/posts', [PostController::class, 'index']);

Route::get('/notifications', [NotificationController::class, 'index']);



