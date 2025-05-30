<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class NotificationSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        // Lấy danh sách user_id từ bảng users
        $userIds = DB::table('users')->pluck('id')->toArray();

        if (empty($userIds)) {
            $this->command->info('Bảng users chưa có dữ liệu. Vui lòng chạy UserSeeder trước.');
            return;
        }

        for ($i = 0; $i < 50; $i++) {
            DB::table('notifications')->insert([
                'user_id' => $faker->randomElement($userIds), // Lấy user_id có thật
                'notification_content' => $faker->sentence(10),
                'notifiable_id' => $faker->numberBetween(1, 1000),
                'notifiable_type' => $faker->randomElement([null, 'App\Models\User', 'App\Models\Post']),
                'is_read' => $faker->boolean(20) ? 1 : 0,
                'data' => json_encode([
                    'url' => $faker->url(),
                    'extra' => $faker->words(3, true)
                ]),
                'created_at' => now(),
                'updated_at' => $faker->boolean(80) ? now() : null,
            ]);
        }
    }
}
