<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'title',
        'details',
        'titleInHindi',
        'titleInGujarati',
        'detailsInHindi',
        'detailsInGujarati',
        'type',
        'status',
        'end_date',
    ];
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
            'end_date' => 'datetime',
        ];
    }
}
