<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Story;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DeleteExpiredStories extends Command
{
    protected $signature = 'stories:delete-expired';
    protected $description = 'Xóa các story đã hết hạn (expires_at nhỏ hơn thời gian hiện tại)';

    public function handle()
    {
        $this->info('Đang xóa các story hết hạn...');

        // Lấy tất cả story có expires_at nhỏ hơn thời gian hiện tại
        $expiredStories = Story::where('expires_at', '<', Carbon::now())->get();

        foreach ($expiredStories as $story) {
            // Xóa tệp hình ảnh liên quan
            if ($story->imageurl) {
                Storage::disk('public')->delete('story_images/' . $story->imageurl);
            }
            // Xóa tệp video liên quan
            if ($story->videourl) {
                Storage::disk('public')->delete('story_videos/' . $story->videourl);
            }

            // Xóa story khỏi cơ sở dữ liệu
            $story->delete();
        }

        $this->info('Đã xóa ' . $expiredStories->count() . ' story hết hạn.');
    }
}
