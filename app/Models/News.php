<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'user_id',
        'language_id',
        'title',
        'slug',
        'short_description',
        'description',
        'image',
        'video',
        'news_type',
        'is_featured',
        'total_views',
        'publish_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'total_views' => 'integer',
            'publish_date' => 'datetime',
            'status' => 'boolean',
        ];
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function language()
    {
        return $this->belongsTo(Language::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function views()
    {
        return $this->hasMany(NewsView::class);
    }

    public function videoEdits()
    {
        return $this->hasMany(VideoEdit::class);
    }
}
