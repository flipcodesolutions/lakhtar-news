<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopReporter extends Model
{
    protected $fillable = [
        'user_id',
        'start_date',
        'end_date',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
