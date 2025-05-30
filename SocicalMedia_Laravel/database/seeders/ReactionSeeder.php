<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class ReactionSeeder extends Seeder
{
    public function run()
    {
        // Tạo đối tượng Faker
        $faker = Faker::create();

        // Tạo 100 dữ liệu mẫu
        foreach (range(1, 250) as $index) {
            DB::table('reactions')->insert([
                'user_id' => $faker->numberBetween(1, 50),  // Người dùng từ 1 đến 50
                'post_id' => '9',  // Bài viết từ 1 đến 20
                'type' => $faker->randomElement(['like', 'love', 'haha', 'wow', 'sad', 'angry']), // Loại cảm xúc ngẫu nhiên
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
