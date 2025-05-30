<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class UpdateUserLastActivity
{
    public function handle($request, Closure $next)
    {
        $user = $request->user(); // Lấy user từ token

        if ($user) {
            $user->last_online_at = now();
            $user->is_online = true;
            $user->save();
        }

        return $next($request);
    }
}
