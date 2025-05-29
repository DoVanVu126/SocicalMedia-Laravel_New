<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;

class CommentSeeder extends Seeder
{
    public function run(): void
    {
        $postIds = Post::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();

        if (empty($postIds) || empty($userIds)) {
            $this->command->warn('Không có bài viết hoặc người dùng nào trong cơ sở dữ liệu.');
            return;
        }

        for ($i = 1; $i <= 100; $i++) {
            Comment::create([
                'post_id' => $postIds[array_rand($postIds)],
                'user_id' => $userIds[array_rand($userIds)],
                'content' => 'Bình luận số ' . $i . ': ' . Str::random(rand(10, 30)),
                'created_at' => now()->subDays(rand(0, 30)),
                'updated_at' => now(),
            ]);
        }
    }
}
