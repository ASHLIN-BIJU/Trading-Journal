<?php

namespace App\Observers;

use App\Models\StatsCache;
use App\Models\Trade;

class TradeObserver
{
    /**
     * Invalidate the stats cache whenever a trade is saved or deleted.
     * The cache will be recomputed lazily on next dashboard load.
     */
    public function saved(Trade $trade): void
    {
        $this->invalidate($trade->user_id);
    }

    public function deleted(Trade $trade): void
    {
        $this->invalidate($trade->user_id);
    }

    private function invalidate(int $userId): void
    {
        StatsCache::where('user_id', $userId)->update(['computed_at' => null]);
    }
}
