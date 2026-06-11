<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsMedia extends Model
{
    protected $fillable = [
        'news_id',
        'media_id',
        'sort_order',
    ];
}
