<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = [
        'media_type',
        'file_path',
        'thumbnail',
        'caption',
        'uploaded_by',
    ];
}
