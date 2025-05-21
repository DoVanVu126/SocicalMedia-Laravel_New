<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Story;
use Carbon\Carbon;

class DeleteExpiredStories extends Command
{
    protected $signature = 'stories:delete-expired';
    protected $description = 'Delete expired stories';

    public function handle()
    {
        $expiredStories = Story::where('expires_at', '<', Carbon::now())->get();
        foreach ($expiredStories as $story) {
            $story->delete();
        }

        $this->info('Expired stories have been deleted!');
    }
}
