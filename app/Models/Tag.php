<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = ['user_id', 'name', 'color', 'category'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function trades(): BelongsToMany
    {
        return $this->belongsToMany(Trade::class, 'trade_tag');
    }
}
