<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class News extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'user_id',
        'title',
        'slug',
        'description',
        'titleInGujarati',
        'descriptionInGujarati',
        'titleInHindi',
        'descriptionInHindi',
        'news_type',
        'is_featured',
        'total_views',
        'publish_date',
        'notification_sent',
        'status',
        'reject_reason',
    ];

    protected function casts(): array
    {
        return [
            'is_featured' => 'boolean',
            'total_views' => 'integer',
            'publish_date' => 'datetime',
            'notification_sent' => 'boolean',
            'status' => 'string',
            'reject_reason' => 'string',
        ];
    }

    public function scopeEligibleForScheduledNotification(Builder $query): Builder
    {
        return $query
            ->where('status', 'approved')
            ->whereNotNull('publish_date')
            ->where('publish_date', '<=', now())
            ->where('notification_sent', false);
    }

    public function isEligibleForScheduledNotification(): bool
    {
        return $this->status === 'approved'
            && $this->publish_date !== null
            && $this->publish_date->lte(now())
            && ! $this->notification_sent;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function likes(): HasMany
    {
        return $this->hasMany(Like::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(NewsView::class);
    }

    public function videoEdits(): HasMany
    {
        return $this->hasMany(VideoEdit::class);
    }

    public function newsMedia(): HasMany
    {
        return $this->hasMany(NewsMedia::class);
    }

    public function media(): BelongsToMany
    {
        return $this->belongsToMany(Media::class, 'news_media')
            ->withPivot('sort_order')
            ->withTimestamps()
            ->orderBy('news_media.sort_order');
    }

    public function getImageAttribute(): ?string
    {
        $imageMedia = $this->relationLoaded('media')
            ? $this->media->firstWhere('media_type', 'image')
            : $this->media()->where('media_type', 'image')->first();

        return $imageMedia?->file_path;
    }

    public function getVideoAttribute(): ?string
    {
        $videoMedia = $this->relationLoaded('media')
            ? $this->media->firstWhere('media_type', 'video')
            : $this->media()->where('media_type', 'video')->first();

        return $videoMedia?->file_path;
    }
}
