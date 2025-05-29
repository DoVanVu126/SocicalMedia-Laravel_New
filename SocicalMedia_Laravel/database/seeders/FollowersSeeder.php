<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FollowersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Xóa dữ liệu cũ nếu cần
        $users = DB::table('users')->pluck('id')->toArray();

        for ($i = 0; $i < 50; $i++) {
            $follower_id = $users[array_rand($users)];
            $followed_id = $users[array_rand($users)];

            if ($follower_id == $followed_id) {
                $followed_id = $users[array_rand($users)];
            }

            DB::table('follows')->insert([
                'follower_id' => $follower_id,
                'followed_id' => $followed_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
