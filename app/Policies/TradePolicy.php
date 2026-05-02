<?php

namespace App\Policies;

use App\Models\Trade;
use App\Models\User;

class TradePolicy
{
    public function view(User $user, Trade $trade): bool
    {
        return $user->id === $trade->user_id;
    }

    public function update(User $user, Trade $trade): bool
    {
        return $user->id === $trade->user_id;
    }

    public function delete(User $user, Trade $trade): bool
    {
        return $user->id === $trade->user_id;
    }
}
