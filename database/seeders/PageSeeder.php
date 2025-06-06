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
                'content' => '
                    <h3>1. Introduction</h3>
                    <p>Welcome to Travela, a marketplace for buying and selling travel itineraries. By using our platform, you agree to these terms and conditions. Please read them carefully.</p>

                    <h3>2. Account Registration and Security</h3>
                    <p>To use Travela, you must:</p>
                    <ul>
                        <li>Be at least 18 years old</li>
                        <li>Provide accurate and complete information</li>
                        <li>Maintain the security of your account</li>
                        <li>Notify us immediately of any unauthorized access</li>
                    </ul>

                    <h3>3. Itinerary Creation and Sales</h3>
                    <p>As an itinerary creator, you:</p>
                    <ul>
                        <li>Must own or have rights to all content in your itineraries</li>
                        <li>Are responsible for the accuracy of your information</li>
                        <li>Must not include illegal or prohibited activities</li>
                        <li>Grant Travela a license to display and distribute your content</li>
                    </ul>

                    <h3>4. Purchasing Itineraries</h3>
                    <p>When purchasing itineraries:</p>
                    <ul>
                        <li>You receive a non-exclusive license to use the itinerary</li>
                        <li>You may not resell or redistribute the itinerary</li>
                        <li>You can modify the itinerary for personal use</li>
                        <li>Refunds are subject to our refund policy</li>
                    </ul>

                    <h3>5. Payment and Payouts</h3>
                    <p>Payment terms:</p>
                    <ul>
                        <li>All prices are in USD unless otherwise stated</li>
                        <li>We use secure third-party payment processors</li>
                        <li>Payouts to creators are processed within 7-14 days</li>
                        <li>We charge a 20% platform fee on all sales</li>
                    </ul>

                    <h3>6. Content Guidelines</h3>
                    <p>Prohibited content includes:</p>
                    <ul>
                        <li>Illegal activities or dangerous recommendations</li>
                        <li>Discriminatory or offensive material</li>
                        <li>False or misleading information</li>
                        <li>Content that infringes on others\' rights</li>
                    </ul>

                    <h3>7. Intellectual Property</h3>
                    <p>Intellectual property rights:</p>
                    <ul>
                        <li>Creators retain ownership of their itineraries</li>
                        <li>Travela has a license to use and display content</li>
                        <li>Users may not copy or redistribute itineraries</li>
                        <li>Report copyright violations to our support team</li>
                    </ul>

                    <h3>8. Limitation of Liability</h3>
                    <p>Travela is not liable for:</p>
                    <ul>
                        <li>Accuracy of itinerary information</li>
                        <li>Travel arrangements or bookings</li>
                        <li>Losses from using purchased itineraries</li>
                        <li>Third-party services or websites</li>
                    </ul>

                    <h3>9. Termination</h3>
                    <p>We may terminate accounts for:</p>
                    <ul>
                        <li>Violation of these terms</li>
                        <li>Fraudulent activity</li>
                        <li>Abuse of the platform</li>
                        <li>Repeated policy violations</li>
                    </ul>

                    <h3>10. Changes to Terms</h3>
                    <p>We may modify these terms at any time. Continued use of the platform constitutes acceptance of modified terms.</p>',
                'meta_title' => 'Terms and Conditions - Travela',
                'meta_description' => 'Read our terms and conditions to understand your rights and responsibilities when using Travela.',
                'is_active' => true,
            ],
            [
                'title' => 'Privacy Policy',
                'slug' => 'privacy',
                'content' => '
                    <h3>1. Information We Collect</h3>
                    <p>We collect the following information:</p>
                    <ul>
                        <li>Account information (name, email, password)</li>
                        <li>Profile information (bio, profile picture)</li>
                        <li>Payment information (processed securely)</li>
                        <li>Travel preferences and history</li>
                        <li>Communication data</li>
                        <li>Usage data and analytics</li>
                    </ul>

                    <h3>2. How We Use Your Information</h3>
                    <p>We use your information to:</p>
                    <ul>
                        <li>Provide and improve our services</li>
                        <li>Process payments and payouts</li>
                        <li>Send important notifications</li>
                        <li>Personalize your experience</li>
                        <li>Prevent fraud and abuse</li>
                        <li>Comply with legal obligations</li>
                    </ul>

                    <h3>3. Information Sharing</h3>
                    <p>We may share your information with:</p>
                    <ul>
                        <li>Payment processors for transactions</li>
                        <li>Service providers who assist our operations</li>
                        <li>Law enforcement when required by law</li>
                        <li>Other users (only with your consent)</li>
                    </ul>

                    <h3>4. Data Security</h3>
                    <p>We protect your information through:</p>
                    <ul>
                        <li>Encryption of sensitive data</li>
                        <li>Secure payment processing</li>
                        <li>Regular security assessments</li>
                        <li>Access controls and monitoring</li>
                    </ul>

                    <h3>5. Your Rights</h3>
                    <p>You have the right to:</p>
                    <ul>
                        <li>Access your personal information</li>
                        <li>Correct inaccurate data</li>
                        <li>Request data deletion</li>
                        <li>Opt-out of marketing communications</li>
                        <li>Export your data</li>
                    </ul>

                    <h3>6. Cookies and Tracking</h3>
                    <p>We use cookies to:</p>
                    <ul>
                        <li>Remember your preferences</li>
                        <li>Analyze website usage</li>
                        <li>Improve our services</li>
                        <li>Provide personalized content</li>
                    </ul>

                    <h3>7. Third-Party Services</h3>
                    <p>We use third-party services for:</p>
                    <ul>
                        <li>Payment processing</li>
                        <li>Analytics and tracking</li>
                        <li>Email communications</li>
                        <li>Cloud storage</li>
                    </ul>

                    <h3>8. Children\'s Privacy</h3>
                    <p>Our services are not intended for users under 18. We do not knowingly collect information from children.</p>

                    <h3>9. International Data Transfers</h3>
                    <p>Your information may be transferred and processed in countries other than your own. We ensure appropriate safeguards are in place.</p>

                    <h3>10. Changes to Privacy Policy</h3>
                    <p>We may update this policy periodically. We will notify you of significant changes.</p>

                    <h3>11. Contact Us</h3>
                    <p>For privacy-related questions or concerns, contact our support team.</p>',
                'meta_title' => 'Privacy Policy - Travela',
                'meta_description' => 'Learn how we collect, use, and protect your personal information on Travela.',
                'is_active' => true,
            ],
            [
                'title' => 'Cookie Policy',
                'slug' => 'cookies',
                'content' => '
                    <h3>1. What Are Cookies</h3>
                    <p>Cookies are small text files stored on your device that help us provide a better user experience. They enable certain features and functionality on our website.</p>

                    <h3>2. Types of Cookies We Use</h3>
                    <p>We use the following types of cookies:</p>
                    <ul>
                        <li>Essential cookies for platform functionality</li>
                        <li>Authentication cookies for secure login</li>
                        <li>Preference cookies to remember your settings</li>
                        <li>Analytics cookies to understand user behavior</li>
                        <li>Marketing cookies for personalized content</li>
                    </ul>

                    <h3>3. How We Use Cookies</h3>
                    <p>Cookies help us:</p>
                    <ul>
                        <li>Keep you signed in</li>
                        <li>Remember your preferences</li>
                        <li>Analyze website usage</li>
                        <li>Improve our services</li>
                        <li>Provide personalized content</li>
                    </ul>

                    <h3>4. Managing Cookies</h3>
                    <p>You can control cookies through:</p>
                    <ul>
                        <li>Your browser settings</li>
                        <li>Our cookie consent banner</li>
                        <li>Third-party opt-out tools</li>
                    </ul>

                    <h3>5. Third-Party Cookies</h3>
                    <p>Some cookies are placed by:</p>
                    <ul>
                        <li>Analytics providers</li>
                        <li>Payment processors</li>
                        <li>Social media platforms</li>
                        <li>Advertising networks</li>
                    </ul>

                    <h3>6. Cookie Duration</h3>
                    <p>Cookies may be:</p>
                    <ul>
                        <li>Session cookies (temporary)</li>
                        <li>Persistent cookies (long-term)</li>
                    </ul>

                    <h3>7. Updates to This Policy</h3>
                    <p>We may update this policy as our practices change. Check back periodically for updates.</p>',
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