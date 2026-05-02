<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeImage extends Model
{
    protected $fillable = ['trade_id', 'image_path', 'image_type', 'caption'];

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->image_path);
    }
}
