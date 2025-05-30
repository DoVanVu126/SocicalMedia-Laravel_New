<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\SavePost;
use Illuminate\Http\Request;

class SavePostController extends Controller
{
    public function list(Request $request)
    {
        $userId = $request->input('user_id');

        $posts = SavePost::where('user_id', $userId)
            ->orderByDesc('created_at')
            ->cursor()
            ->map(function ($item) {
                $save_post = $item->toArray();

                $post = Post::where('id', $item->post_id)
                    ->orderByDesc('id')
                    ->first();

                $save_post['post'] = $post->toArray();

                return $save_post;
            })->toArray();

        $data = returnMessage(1, $posts, 'Success');
        return response($data, 200);
    }

    public function createOrDelete(Request $request)
    {
        try {
            $userId = $request->input('user_id');
            $postId = $request->input('post_id');

            $save_post = SavePost::where('user_id', $userId)
                ->where('post_id', $postId)
                ->first();

            if ($save_post) {
                $save_post->delete();
            } else {
                $save_post = new SavePost();
                $save_post->user_id = $userId;
                $save_post->post_id = $postId;
                $save_post->save();
            }
            $data = returnMessage(1, '', 'Success!');
            return response($data, 200);
        } catch (\Exception $ex) {
            $data = returnMessage(-1, '', $ex->getMessage());
            return response($data, 400);
        }
    }
}
