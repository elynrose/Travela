<!DOCTYPE html>
<html lang="en">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travela - Discover Your Next Adventure</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
            <style>
        .hero-banner {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('{{ asset('images/hero-banner.jpg') }}') center/cover no-repeat;
            height: 500px;
            color: #fff;
            display: flex;
            align-items: center;
        }
        .travela-section {
            padding: 60px 0;
        }
        .travela-section.bg-light {
            background: #f8f9fa;
        }
        .travela-footer {
            background: #222;
            color: #fff;
            padding: 40px 0 20px 0;
        }
        .travela-footer a { color: #fff; text-decoration: underline; }
        .travela-footer a:hover { color: #ffc107; }
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .navbar-brand {
            font-weight: 600;
            color: #333;
        }
        .nav-link {
            color: #666;
            font-weight: 500;
        }
        .nav-link:hover {
            color: #ffc107;
        }
        .btn-outline-warning:hover {
            color: #fff;
        }
            </style>
    </head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">
                <i class="bi bi-globe2 text-warning"></i> Travela
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('itineraries.index') }}">Explore</a>
                    </li>
                    @auth
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="nav-link border-0 bg-transparent">Logout</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">Register</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- Add some padding to account for fixed navbar -->
    <div style="padding-top: 76px;"></div>

    <!-- Banner -->
    <section class="hero-banner">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <h1 class="display-3 fw-bold mb-3">Your Travel Journey Made Simple</h1>
                    <p class="lead mb-4">Buy and sell detailed travel itineraries. Let experienced travelers guide your next adventure.</p>
                    <a href="{{ route('itineraries.index') }}" class="btn btn-warning btn-lg">Browse Itineraries</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Introduction -->
    <section class="travela-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <img src="{{ asset('images/travel-experience.jpg') }}" alt="Travel Experience" class="img-fluid rounded shadow">
                </div>
                <div class="col-lg-6">
                    <h2 class="h3 mb-4">Why Plan When You Can Follow?</h2>
                    <p class="lead mb-4">Travela is a marketplace where experienced travelers share their proven itineraries with those who want to travel without the hassle of planning.</p>
                    <p class="mb-4">Whether you're a traveler looking to share your expertise or someone who wants to follow a well-crafted journey, Travela connects you with the perfect travel experience.</p>
                    <a href="{{ route('itineraries.index') }}" class="btn btn-outline-warning">Explore Itineraries</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Why Travela -->
    <section class="travela-section">
        <div class="container">
            <h2 class="text-center mb-5">Why Choose Travela?</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="display-4 text-warning mb-3">
                            <i class="bi bi-map"></i>
                        </div>
                        <h3 class="h5 mb-3">Proven Itineraries</h3>
                        <p class="text-muted">Follow itineraries created by experienced travelers who have already done the journey.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="display-4 text-warning mb-3">
                            <i class="bi bi-cash-stack"></i>
                        </div>
                        <h3 class="h5 mb-3">Earn While Sharing</h3>
                        <p class="text-muted">Share your travel expertise and earn money by selling your itineraries to other travelers.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="display-4 text-warning mb-3">
                            <i class="bi bi-clock"></i>
                        </div>
                        <h3 class="h5 mb-3">Save Time</h3>
                        <p class="text-muted">Skip the planning phase and start enjoying your trip with ready-to-follow itineraries.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="travela-section bg-light">
        <div class="container">
            <h2 class="text-center mb-5">How It Works</h2>
            <div class="row g-4">
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="display-4 text-warning mb-3">1</div>
                        <h3 class="h5 mb-3">Browse</h3>
                        <p class="text-muted">Explore itineraries created by experienced travelers.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="display-4 text-warning mb-3">2</div>
                        <h3 class="h5 mb-3">Purchase</h3>
                        <p class="text-muted">Buy the itinerary that matches your travel style.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="display-4 text-warning mb-3">3</div>
                        <h3 class="h5 mb-3">Follow</h3>
                        <p class="text-muted">Get detailed day-by-day plans and follow the journey.</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <div class="display-4 text-warning mb-3">4</div>
                        <h3 class="h5 mb-3">Enjoy</h3>
                        <p class="text-muted">Experience your trip without the stress of planning.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQs -->
    <section class="travela-section">
        <div class="container">
            <h2 class="text-center mb-5">Frequently Asked Questions</h2>
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion" id="faqAccordion">
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                    What is a travel itinerary?
                                </button>
                            </h3>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    A travel itinerary is a detailed day-by-day plan of your trip, including accommodations, activities, meals, and transportation. It's like having a local guide's knowledge in your pocket.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    How do I sell my itinerary?
                                </button>
                            </h3>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Create an account, click "Create Itinerary," and fill in the details of your journey. Include day-by-day plans, accommodation recommendations, and local tips. Once approved, your itinerary will be available for purchase.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    What's included in a purchased itinerary?
                                </button>
                            </h3>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Each itinerary includes detailed day-by-day plans, accommodation recommendations, activity suggestions, local tips, and estimated costs. You'll receive everything you need to follow the journey.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                    Can I modify a purchased itinerary?
                                </button>
                            </h3>
                            <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes! While the itinerary provides a complete plan, you can customize it to your preferences. Add or remove activities, change accommodations, or adjust the schedule to suit your needs.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="travela-footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-3">Travela</h5>
                    <p>Your trusted platform for discovering and sharing amazing travel experiences.</p>
                </div>
                <div class="col-md-4 mb-4 mb-md-0">
                    <h5 class="mb-3">Quick Links</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('home') }}">Home</a></li>
                        <li class="mb-2"><a href="{{ route('itineraries.index') }}">Browse Itineraries</a></li>
                        <li class="mb-2"><a href="{{ route('contact') }}">Contact Us</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5 class="mb-3">Legal</h5>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('pages.terms') }}">Terms & Conditions</a></li>
                        <li class="mb-2"><a href="{{ route('pages.privacy') }}">Privacy Policy</a></li>
                        <li class="mb-2"><a href="{{ route('pages.cookies') }}">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>
            <hr class="my-4" style="border-color: rgba(255,255,255,0.1);">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <p class="mb-0">&copy; {{ date('Y') }} Travela. All rights reserved.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <div class="social-links">
                        <a href="#" class="me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="me-3"><i class="bi bi-twitter"></i></a>
                        <a href="#" class="me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
