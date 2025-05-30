<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Carbon\Carbon;

class CheckInactiveApiUsers extends Command
{
    protected $signature = 'api-users:check-inactive';
    protected $description = 'Tắt trạng thái online nếu user không tương tác API trong 5 phút';

    public function handle()
    {
        $inactiveUsers = User::where('is_online', true)
            ->where('last_online_at', '<', now()->subMinutes(5))
            ->update(['is_online' => false]);

        $this->info("Đã cập nhật $inactiveUsers user không hoạt động.");
    }
}

