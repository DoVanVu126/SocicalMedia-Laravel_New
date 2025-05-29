<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class NotificationUserSeeder extends Seeder
{
    public function run()
    {
        $faker = Faker::create();

        $userIds = DB::table('users')->pluck('id')->toArray();
        $notificationIds = DB::table('notifications')->pluck('id')->toArray();

        if (empty($userIds) || empty($notificationIds)) {
            $this->command->info('Bảng users hoặc notifications chưa có dữ liệu. Vui lòng chạy UserSeeder và NotificationSeeder trước.');
            return;
        }

        for ($i = 0; $i < 50; $i++) {
            DB::table('notification_user')->insert([
                'notification_id' => $faker->randomElement($notificationIds),
                'user_id' => $faker->randomElement($userIds),
                'is_read' => $faker->boolean(50),
                'is_deleted' => $faker->boolean(10) ? 1 : 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
