<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserBookmark extends Model
{
    protected $fillable = [
        'user_id',
        'news_id',
    ];

    public function news()
    {
        return $this->belongsTo(News::class, 'news_id');
    }
}
