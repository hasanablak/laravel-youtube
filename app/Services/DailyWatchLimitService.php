<?php

namespace App\Services;

use App\Models\WatchHistory;

class DailyWatchLimitService
{
    public function getDailyLimit(): int
    {
        return (int) config('app.daily_video_watch_limit', 3);
    }

    public function getDailyWatchCount(?int $userId): int
    {
        if (!$userId) {
            return 0;
        }
        return WatchHistory::query()
            ->where('user_id', $userId)
            ->whereDate('watched_at', now()->toDateString())
            ->count();
    }

    public function getStatus(?int $userId): array
    {
        $dailyLimit = $this->getDailyLimit();
        $dailyWatchCount = $this->getDailyWatchCount($userId);
        $remaining = max(0, $dailyLimit - $dailyWatchCount);

        return [
            'daily_limit' => $dailyLimit,
            'daily_watch_count' => $dailyWatchCount,
            'remaining' => $remaining,
            'limit_exceeded' => $dailyWatchCount >= $dailyLimit,
        ];
    }
}
