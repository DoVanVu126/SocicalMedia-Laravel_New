<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Post;
use App\Models\User;
use Illuminate\Support\Str;

class PostSeeder extends Seeder
{
    public function run(): void
    {
        // Lấy tất cả ID của user hiện có
        $userIds = User::pluck('id')->toArray();

        for ($i = 1; $i <= 10; $i++) {
            Post::create([
                'user_id' => 9, // Lấy ngẫu nhiên một user_id
                'content' => 'Áo số ' . $i,
                'imageurl' => rand(1, 10) . '.jpg',
                'videourl' => null,
                'status' => 'published',
                'visibility' => 'public',
                'reaction_summary' => null,
                'created_at' => now()->subDays(rand(0, 30)), // thời gian ngẫu nhiên trong 30 ngày qua
                'updated_at' => now(),
            ]);
        }
    }
}
