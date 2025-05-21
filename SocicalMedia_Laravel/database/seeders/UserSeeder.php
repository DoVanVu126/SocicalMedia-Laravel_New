<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run()
    {
        // Tạo đối tượng Faker
        $faker = Faker::create();

        // Tạo 1000 người dùng
        foreach (range(1, 1000) as $index) {
            DB::table('users')->insert([
                'username' => $faker->userName,  // Tạo username ngẫu nhiên
                'password' => '123456',  // Mật khẩu mặc định đã mã hóa
                'profilepicture' => $faker->imageUrl(640, 480, 'people'),  // Đường dẫn ảnh ngẫu nhiên
                'email' => $faker->unique()->safeEmail,  // Email ngẫu nhiên và duy nhất
                'phone' => $faker->phoneNumber,  // Số điện thoại ngẫu nhiên
                'created_at' => now(),  // Thời gian tạo
                'updated_at' => now(),  // Thời gian cập nhật
                'two_factor_enabled' => $faker->boolean(50),  // Ngẫu nhiên bật hoặc tắt 2FA
            ]);
        }
    }
}
