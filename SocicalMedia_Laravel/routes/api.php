<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReactionController;

// Đăng ký & đăng nhập
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Lấy danh sách bài viết
Route::get('/posts', [PostController::class, 'index']);

// Thêm reaction (cho post)
Route::post('/reactions', [PostController::class, 'addReaction']);

// Cập nhật reaction (thay đổi biểu tượng cảm xúc)
Route::post('/posts/{postId}/reactions/update', [PostController::class, 'updateReaction']);

// Xóa reaction
Route::delete('/posts/{postId}/reactions/{reactionId}', [PostController::class, 'removeReaction']);

// Lấy danh sách thông báo (nếu có route này, nếu không thì bỏ qua)
Route::get('/notifications', [NotificationController::class, 'index']);
