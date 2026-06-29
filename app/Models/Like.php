<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    protected $fillable = [
        'user_id',
        'news_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function news()
    {
        return $this->belongsTo(News::class);
    }
}
