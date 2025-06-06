<?php

use App\Http\Controllers\FavouriteController;
use App\Http\Controllers\FriendRequestController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\SavePostController;
use App\Http\Controllers\StoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserSearchController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Các route công khai
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);

// Posts
Route::get('/posts', [PostController::class, 'index']);
Route::get('/users/{userId}/posts', [PostController::class, 'getUserPosts']);
Route::get('/posts/{id}', [PostController::class, 'show']);
Route::post('/posts', [PostController::class, 'store']);
Route::put('/posts/{id}', [PostController::class, 'update']);
Route::delete('/posts/{id}', [PostController::class, 'destroy']);
Route::patch('/posts/{id}/status', [PostController::class, 'changeStatus']);
Route::post('/posts/{id}/react', [PostController::class, 'react']);
Route::delete('/posts/{id}/react', [PostController::class, 'removeReaction']);

// Comments
Route::get('/posts/{postId}/comments', [CommentController::class, 'index']);
Route::post('/posts/{postId}/comments', [CommentController::class, 'store']);
Route::put('/posts/{postId}/comments/{commentId}', [CommentController::class, 'update']);
Route::delete('/posts/{postId}/comments/{commentId}', [CommentController::class, 'destroy']);

// Notifications
Route::get('/notifications/{userId}', [NotificationController::class, 'index']);
Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
Route::post('/notifications/settings', [NotificationController::class, 'toggleSettings']);
Route::delete('/notifications/{id}', [NotificationController::class, 'destroy']);
Route::get('/notifications/unread/{userId}', [NotificationController::class, 'getUnread']);

// Reactions
Route::post('/reactions', [ReactionController::class, 'store']);
Route::get('/posts/{postId}/reactions', [ReactionController::class, 'index']);

// Follows
Route::post('/follow', [FollowController::class, 'follow']);
Route::post('/unfollow', [FollowController::class, 'unfollow']);
Route::post('/follow-status', [FollowController::class, 'check']);
Route::get('/users/{userId}/{type}', [FollowController::class, 'list']);
Route::get('/users/{id}/followers', [FollowController::class, 'getFollowers']);
Route::get('/users/{id}/following', [FollowController::class, 'getFollowing']);

// Admin Users
Route::get('/users/list', [AdminUserController::class, 'list'])->name('api.admin.users.list');
Route::get('/users/detail/{id}', [AdminUserController::class, 'detail'])->name('api.admin.users.detail');
Route::post('/users/create', [AdminUserController::class, 'create'])->name('api.admin.users.create');
Route::post('/users/update/{id}', [AdminUserController::class, 'update'])->name('api.admin.users.update');
Route::delete('/users/delete/{id}', [AdminUserController::class, 'delete'])->name('api.admin.users.delete');

// Users
Route::get('/users/search', [UserSearchController::class, 'suggest']);
Route::get('/users/{id}', [UserSearchController::class, 'getUser']);
Route::get('/users/find/{id}', [UserSearchController::class, 'getUser']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::put('/users/{id}/bio', [UserController::class, 'updateBio']);
Route::post('/users/{id}/profile-picture', [UserController::class, 'updateProfilePicture']);

// Friends
Route::get('/friends/list', [FriendRequestController::class, 'getListFriend'])->name('api.admin.friends.list');
Route::get('/friends/request', [FriendRequestController::class, 'getListRequest'])->name('api.admin.friends.show.request');
Route::get('/friends/pending', [FriendRequestController::class, 'getListPending'])->name('api.admin.friends.show.pending');
Route::get('/friends/no-friends', [FriendRequestController::class, 'getAllNoFriend'])->name('api.admin.friends.no.friends');
Route::post('/friends/store', [FriendRequestController::class, 'store'])->name('api.admin.friends.store');
Route::post('/friends/accept', [FriendRequestController::class, 'accept'])->name('api.admin.friends.accept');
Route::post('/friends/reject', [FriendRequestController::class, 'reject'])->name('api.admin.friends.reject');

// Stories
Route::get('/stories', [StoryController::class, 'index']);
Route::post('/stories', [StoryController::class, 'store']);
Route::put('/stories/{id}', [StoryController::class, 'update']);
Route::delete('/stories/{id}', [StoryController::class, 'destroy']);

Route::get('/post/favorites/list', [FavouriteController::class, 'list'])->name('api.favorites.post.list');
Route::post('/post/favorites/store', [FavouriteController::class, 'createOrDelete'])->name('api.favorites.post.store');

Route::get('/post/saves/list', [SavePostController::class, 'list'])->name('api.saves.post.list');
Route::post('/post/saves/store', [SavePostController::class, 'createOrDelete'])->name('api.saves.post.store');

Route::post('/post/share', [PostController::class, 'sharePost'])->name('api.post.share');

Route::get('/logout', [AuthController::class, 'logout']);
