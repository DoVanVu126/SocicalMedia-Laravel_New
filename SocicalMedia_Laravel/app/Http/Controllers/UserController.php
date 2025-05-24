<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Story;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function show($id)
    {
        $user = User::withCount(['followers', 'followings', 'posts'])->find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Check if the user has active stories
        $hasActiveStories = Story::where('user_id', $id)
            ->where('expires_at', '>', now())
            ->exists();

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
            'has_active_stories' => $hasActiveStories, // Added to indicate active stories
        ]);
    }

    public function updateBio(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Update bio
        $user->bio = $request->input('bio');
        $user->save();

        return response()->json(['message' => 'Bio updated successfully']);
    }

    public function updateProfilePicture(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Validate the uploaded file
        $request->validate([
            'profilepicture' => 'required|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
        ]);

        if ($request->hasFile('profilepicture')) {
            // Delete old profile picture if it exists
            if ($user->profilepicture && Storage::exists('public/images/' . $user->profilepicture)) {
                Storage::delete('public/images/' . $user->profilepicture);
            }

            // Store new profile picture
            $file = $request->file('profilepicture');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('public/images', $filename);

            // Update profilepicture field
            $user->profilepicture = $filename;
            $user->save();

            return response()->json([
                'message' => 'Profile picture updated successfully',
                'profilepicture' => $filename,
            ]);
        }

        return response()->json(['message' => 'No file uploaded'], 400);
    }
}
