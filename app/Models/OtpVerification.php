<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OtpVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'mobile',
        'otp',
        'expire_at',
        'is_verified',
    ];

    protected function casts(): array
    {
        return [
            'expire_at' => 'datetime',
            'is_verified' => 'boolean',
        ];
    }
}
