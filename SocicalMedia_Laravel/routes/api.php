<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use Illuminate\Support\Facades\Route;

// Các route công khai
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/posts', [PostController::class, 'index']);
Route::get('/posts/{id}', [PostController::class, 'show']);
Route::post('/posts', [PostController::class, 'store']); // Tạo bài viết mới
Route::put('/posts/{id}', [PostController::class, 'update']); // Cập nhật bài viết (dùng PUT thay vì POST)
Route::delete('/posts/{id}', [PostController::class, 'destroy']);
Route::patch('/posts/{id}/status', [PostController::class, 'changeStatus']);
Route::post('/posts/{id}/react', [PostController::class, 'react']);
Route::delete('/posts/{id}/react', [PostController::class, 'removeReaction']);
Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
Route::post('/posts/{postId}/comments', [CommentController::class, 'store']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
