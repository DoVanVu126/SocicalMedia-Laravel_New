<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class StorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            DB::table('stories')->insert([
                'user_id' => rand(1, 2), // Giả sử có sẵn user_id từ 1 đến 10
               'imageurl' => rand(1, 10) . '.jpg',
                'videourl' => null,
                'content' => 'Nội dung story số ' . $i,
                'visibility' => rand(0, 1) ? 'public' : 'private',
                'expires_at' => Carbon::now()->addHours(rand(1, 48)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
