<?php

namespace App\Http\Controllers;

use App\Models\Favourites;
use App\Models\Post;
use Illuminate\Http\Request;

class FavouriteController extends Controller
{
    public function list(Request $request)
    {
        $userId = $request->input('user_id');

        $posts = Favourites::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->cursor()
            ->map(function ($item) {
                $favourite = $item->toArray();

                $post = Post::where('id', $item->post_id)
                    ->orderByDesc('id')
                    ->first();

                $favourite['post'] = $post->toArray();

                return $favourite;
            })->toArray();

        $data = returnMessage(1, $posts, 'Success');
        return response($data, 200);
    }

    public function createOrDelete(Request $request)
    {
        try {
            $userId = $request->input('user_id');
            $postId = $request->input('post_id');

            $favourite = Favourites::where('user_id', $userId)
                ->where('post_id', $postId)
                ->first();

            if ($favourite) {
                $favourite->delete();
            } else {
                $favourite = new Favourites();
                $favourite->user_id = $userId;
                $favourite->post_id = $postId;
                $favourite->save();
            }
            $data = returnMessage(1, '', 'Success!');
            return response($data, 200);
        } catch (\Exception $ex) {
            $data = returnMessage(-1, '', $ex->getMessage());
            return response($data, 400);
        }
    }
}
