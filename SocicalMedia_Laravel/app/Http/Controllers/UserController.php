<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;

use App\Models\User;

class UserController extends Controller
{
    public function show($id)
    {
        $user = User::withCount(['followers', 'followings', 'posts'])->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        return response()->json([
            'id' => $user->id,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'profilepicture' => $user->profilepicture,
            'bio' => $user->bio,
            'followers_count' => $user->followers_count,
            'following_count' => $user->followings_count,
            'posts' => $user->posts,
        ]);
    }

    public function updateBio(Request $request, $id)
{
    $user = User::find($id);

    if (!$user) {
        return response()->json(['message' => 'User not found'], 404);
    }

    // Cập nhật bio
    $user->bio = $request->input('bio');
    $user->save();

    return response()->json(['message' => 'Bio updated successfully']);
}
}
