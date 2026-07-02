<?php

namespace Database\Seeders;

use App\Models\Cms;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CmsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Cms::create([
            'slug' => 'privacy-policy',
            'title' => 'Privacy Policy',
            'detail' => 'Privacy Policy',
        ]);
        Cms::create([
            'slug' => 'terms-and-conditions',
            'title' => 'Terms and Conditions',
            'detail' => 'Terms and Conditions',
        ]);
    }
}
