<?php

use App\Http\Controllers\PostController;
use Illuminate\Support\Facades\Route;

// Nếu không cần auth, tạm thời dùng route public:
Route::get('/posts', [PostController::class, 'index']);

