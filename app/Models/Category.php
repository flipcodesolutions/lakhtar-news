<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'status',
        'nameInHindi',
        'nameInGujarati',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function news()
    {
        return $this->hasMany(News::class);
    }

    public function favoritedByUsers()
    {
        return $this->belongsToMany(User::class, 'user_favorite_categories')->withTimestamps();
    }
}
