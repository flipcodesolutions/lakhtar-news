<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchHistory extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_DELETED = 'deleted';

    protected $fillable = [
        'user_id',
        'news_id',
        'status',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function news()
    {
        return $this->belongsTo(News::class, 'news_id');
    }
}
