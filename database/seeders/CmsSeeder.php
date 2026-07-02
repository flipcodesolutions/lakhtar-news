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
            'detail' => 'We value your privacy and are committed to protecting your personal information. Our News Application may collect basic information such as your name, email address, and device details to provide and improve our services. We use this information to personalize your experience, send important updates, and enhance app performance. We do not sell, rent, or share your personal information with third parties except as required by law or to provide our services. The app may use cookies or similar technologies to improve functionality and analyze usage. We take reasonable security measures to protect your data from unauthorized access or misuse. You may choose to disable notifications or delete your account at any time. Our services are not intended for children under the age of 13. By using this application, you agree to the collection and use of your information as described in this Privacy Policy. If you have any questions or concerns, please contact us through the support details provided within the application.',
        ]);
        Cms::create([
            'slug' => 'terms-and-conditions',
            'title' => 'Terms and Conditions',
            'detail' => 'By accessing and using our News Application, you agree to comply with these Terms and Conditions. The content provided in the app is for informational purposes only and should not be considered professional advice. You agree to use the application lawfully and not engage in any activity that may disrupt or harm the service. All news articles, images, logos, and other content are protected by applicable intellectual property laws and may not be copied or distributed without permission. We reserve the right to modify, suspend, or discontinue any part of the application at any time without prior notice. While we strive to provide accurate and up-to-date information, we do not guarantee the completeness or accuracy of all content. Users are responsible for maintaining the confidentiality of their account information, if applicable. We are not liable for any direct or indirect damages arising from the use of the application or its content. Continued use of the application after any updates to these terms constitutes your acceptance of the revised Terms and Conditions. If you do not agree with these terms, you should discontinue using the application immediately.',
        ]);
    }
}
