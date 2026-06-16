<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'mobile',
        'email',
        'password',
        'role',
        'language',
        'profile_image',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function favoriteCategories()
    {
        return $this->belongsToMany(Category::class, 'user_favorite_categories')->withTimestamps();
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function newsViews()
    {
        return $this->hasMany(NewsView::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function videoEdits()
    {
        return $this->hasMany(VideoEdit::class);
    }

    public function watchHistories()
    {
        return $this->hasMany(WatchHistory::class);
    }
}
