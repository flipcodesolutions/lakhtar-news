<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'app_name',
        'logo',
        'contact_email',
        'contact_mobile',
        'privacy_policy',
        'terms_conditions',
        'about_us',
    ];
}
