<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserSearchController;
use App\Http\Controllers\AdminUserController;

use App\Http\Controllers\ReactionController;

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
Route::get('notifications/{userId}', [NotificationController::class, 'index']);
Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
Route::post('notifications/settings', [NotificationController::class, 'toggleSettings']);
Route::delete('notifications/{id}', [NotificationController::class, 'destroy']);



 Route::get('users/list', [AdminUserController::class, 'list'])->name('api.admin.users.list');
    Route::get('users/detail/{id}', [AdminUserController::class, 'detail'])->name('api.admin.users.detail');
    Route::post('users/create', [AdminUserController::class, 'create'])->name('api.admin.users.create');
    Route::post('users/update/{id}', [AdminUserController::class, 'update'])->name('api.admin.users.update');
    Route::delete('users/delete/{id}', [AdminUserController::class, 'delete'])->name('api.admin.users.delete');

//story
Route::get('/stories', [StoryController::class, 'index']);
Route::post('/stories', [StoryController::class, 'store']);
Route::put('/stories/{id}', [StoryController::class, 'update']);
Route::delete('/stories/{id}', [StoryController::class, 'destroy']);


Route::get('/users/search', [UserSearchController::class, 'suggest']);

Route::get('/users/{id}', [UserSearchController::class, 'getUser']);

Route::get('/users/find/{id}', [UserSearchController::class, 'getUser']);



Route::get('/posts/{postId}/reactions', [ReactionController::class, 'index']);


Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

