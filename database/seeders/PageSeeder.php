<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'title' => 'Terms and Conditions',
                'slug' => 'terms',
                'content' => '<h2>Introduction</h2><p>Welcome to Travela. By using our platform, you agree to these terms and conditions.</p><h2>User Accounts</h2><p>You must be at least 18 years old to create an account. You are responsible for maintaining the security of your account.</p><h2>Payments and Payouts</h2><p>All payments are processed securely through our payment providers. Payouts are subject to our verification process.</p><h2>Content Guidelines</h2><p>Users must not post illegal, offensive, or inappropriate content. We reserve the right to remove any content that violates our guidelines.</p><h2>Intellectual Property</h2><p>Users retain ownership of their content but grant us a license to use it for platform purposes.</p><h2>Limitation of Liability</h2><p>We are not liable for any indirect, incidental, or consequential damages arising from your use of our platform.</p><h2>Changes to Terms</h2><p>We may modify these terms at any time. Continued use of the platform constitutes acceptance of modified terms.</p>',
                'meta_title' => 'Terms and Conditions - Travela',
                'meta_description' => 'Read our terms and conditions to understand your rights and responsibilities when using Travela.',
                'is_active' => true,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy',
                'content' => '<h2>Information We Collect</h2><p>We collect personal information such as your name, email address, and payment details when you use our platform.</p><h2>How We Use Your Information</h2><p>We use your information to provide our services, process payments, and improve your experience.</p><h2>Information Sharing</h2><p>We do not sell your personal information. We may share it with service providers who assist in operating our platform.</p><h2>Data Security</h2><p>We implement security measures to protect your personal information from unauthorized access.</p><h2>Your Rights</h2><p>You have the right to access, correct, or delete your personal information.</p><h2>Cookies</h2><p>We use cookies to improve your browsing experience and analyze platform usage.</p><h2>Contact Us</h2><p>If you have questions about our privacy policy, please contact us.</p>',
                'meta_title' => 'Privacy Policy - Travela',
                'meta_description' => 'Learn how we collect, use, and protect your personal information on Travela.',
                'is_active' => true,
            ],
            [
                'title' => 'Cookie Policy',
                'slug' => 'cookies',
                'content' => '<h2>What Are Cookies</h2><p>Cookies are small text files stored on your device that help us provide a better user experience.</p><h2>Types of Cookies We Use</h2><ul><li>Essential cookies for platform functionality</li><li>Analytics cookies to understand user behavior</li><li>Marketing cookies for personalized content</li></ul><h2>How We Use Cookies</h2><p>We use cookies to remember your preferences, analyze platform usage, and improve our services.</p><h2>Managing Cookies</h2><p>You can control cookie settings through your browser preferences.</p><h2>Third-Party Cookies</h2><p>Some cookies are placed by third-party services we use, such as analytics providers.</p><h2>Updates to This Policy</h2><p>We may update this policy as our practices change.</p>',
                'meta_title' => 'Cookie Policy - Travela',
                'meta_description' => 'Learn about how we use cookies to enhance your experience on Travela.',
                'is_active' => true,
            ],
        ];

        foreach ($pages as $page) {
            Page::updateOrCreate(
                ['slug' => $page['slug']],
                $page
            );
        }
    }
} 