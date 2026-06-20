<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_type',
        'file_path',
        'thumbnail',
        'caption',
        'uploaded_by',
    ];

    public function news(): BelongsToMany
    {
        return $this->belongsToMany(News::class, 'news_media')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
