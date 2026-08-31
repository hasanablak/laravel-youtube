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

    public function hasWatchedVideoToday(?int $userId, ?int $videoId): bool
    {
        if (!$userId || !$videoId) {
            return false;
        }

        return WatchHistory::query()
            ->where('user_id', $userId)
            ->where('video_id', $videoId)
            ->whereDate('watched_at', now()->toDateString())
            ->exists();
    }

    public function getStatus(?int $userId, ?int $videoId = null): array
    {
        $dailyLimit = $this->getDailyLimit();
        $dailyWatchCount = $this->getDailyWatchCount($userId);
        $remaining = max(0, $dailyLimit - $dailyWatchCount);
        $limitExceeded = $dailyWatchCount >= $dailyLimit;
        $alreadyWatchedToday = $this->hasWatchedVideoToday($userId, $videoId);

        return [
            'daily_limit' => $dailyLimit,
            'daily_watch_count' => $dailyWatchCount,
            'remaining' => $remaining,
            'limit_exceeded' => $limitExceeded,
            'already_watched_today' => $alreadyWatchedToday,
            'can_watch' => !$limitExceeded || $alreadyWatchedToday,
        ];
    }
}
