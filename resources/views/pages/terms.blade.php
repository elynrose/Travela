<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">Terms and Conditions</h2>
    </x-slot>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h1 class="h3 mb-4">Terms and Conditions</h1>
                        <p class="text-muted">Last updated: {{ now()->format('F d, Y') }}</p>

                        <div class="mb-5">
                            <h2 class="h4 mb-3">1. Introduction</h2>
                            <p>Welcome to Travela. These terms and conditions outline the rules and regulations for the use of our website and services.</p>
                            <p>By accessing this website, we assume you accept these terms and conditions in full. Do not continue to use Travela if you do not accept all of the terms and conditions stated on this page.</p>
                        </div>

                        <div class="mb-5">
                            <h2 class="h4 mb-3">2. User Accounts</h2>
                            <p>To access certain features of our platform, you must register for an account. You agree to:</p>
                            <ul>
                                <li>Provide accurate and complete information</li>
                                <li>Maintain the security of your account</li>
                                <li>Notify us immediately of any unauthorized use</li>
                                <li>Accept responsibility for all activities under your account</li>
                            </ul>
                        </div>

                        <div class="mb-5">
                            <h2 class="h4 mb-3">3. Itinerary Creation and Sales</h2>
                            <p>As a creator on our platform, you agree to:</p>
                            <ul>
                                <li>Create accurate and detailed itineraries</li>
                                <li>Maintain the quality of your content</li>
                                <li>Comply with all applicable laws and regulations</li>
                                <li>Not engage in fraudulent or misleading practices</li>
                            </ul>
                        </div>

                        <div class="mb-5">
                            <h2 class="h4 mb-3">4. Payments and Payouts</h2>
                            <p>Our payment and payout terms include:</p>
                            <ul>
                                <li>Secure payment processing through Stripe</li>
                                <li>Clear payout schedules and requirements</li>
                                <li>Transparent fee structure</li>
                                <li>Refund policies and procedures</li>
                            </ul>
                        </div>

                        <div class="mb-5">
                            <h2 class="h4 mb-3">5. Intellectual Property</h2>
                            <p>All content on this website, including but not limited to text, graphics, logos, and software, is the property of Travela or its content suppliers and is protected by international copyright laws.</p>
                        </div>

                        <div class="mb-5">
                            <h2 class="h4 mb-3">6. Limitation of Liability</h2>
                            <p>Travela shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of or inability to use the service.</p>
                        </div>

                        <div class="mb-5">
                            <h2 class="h4 mb-3">7. Changes to Terms</h2>
                            <p>We reserve the right to modify these terms at any time. We will notify users of any material changes via email or through the website.</p>
                        </div>

                        <div class="mb-5">
                            <h2 class="h4 mb-3">8. Contact Information</h2>
                            <p>If you have any questions about these Terms and Conditions, please contact us at:</p>
                            <ul class="list-unstyled">
                                <li>Email: support@travela.com</li>
                                <li>Phone: +1 (555) 123-4567</li>
                                <li>Address: 123 Travel Street, City, Country</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 