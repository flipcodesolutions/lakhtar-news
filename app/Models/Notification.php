<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Notification extends Model
{
    use HasFactory;

    public const TYPE_NEWS_APPROVED = 'news_approved';

    public const TYPE_NEW_ALERT = 'new_alert';

    public const TYPE_NEWS_REJECTED = 'news_rejected';

    public const TYPE_NEW_COMMENT = 'new_comment';

    public const TYPE_COMMENT_REPORTED = 'comment_reported';

    public const AUDIENCE_USER = 'user';

    public const AUDIENCE_REPORTER = 'reporter';

    protected $fillable = [
        'title',
        'message',
        'type',
        'reference_type',
        'reference_id',
        'audience',
    ];

    public function userNotifications(): HasMany
    {
        return $this->hasMany(UserNotification::class);
    }
}
