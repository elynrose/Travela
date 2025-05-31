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
                    <h1 class="display-3 fw-bold mb-3">Discover Your Next Adventure</h1>
                    <p class="lead mb-4">Explore handcrafted travel experiences that combine luxury, adventure, and authentic local culture.</p>
                    <a href="{{ route('itineraries.index') }}" class="btn btn-warning btn-lg">Start Exploring</a>
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
                    <h2 class="h3 mb-4">Your Journey, Your Story</h2>
                    <p class="lead mb-4">At Travela, we believe that every journey should be as unique as the traveler. Our carefully curated itineraries are designed to create unforgettable experiences that go beyond the ordinary.</p>
                    <p class="mb-4">Whether you're seeking adventure in the wild, cultural immersion in historic cities, or relaxation in paradise, we've got you covered with personalized travel experiences that match your dreams.</p>
                    <a href="{{ route('itineraries.index') }}" class="btn btn-outline-warning">Explore Our Destinations</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Itineraries -->
    <section class="travela-section bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Featured Adventures</h2>
            <div class="row g-4">
                @isset($featuredItineraries)
                    @foreach($featuredItineraries as $itinerary)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-sm">
                                @if($itinerary->getCoverImageUrl())
                                    <img src="{{ $itinerary->getCoverThumbUrl() }}" class="card-img-top" alt="{{ $itinerary->title }}" style="height: 200px; object-fit: cover;">
        @endif
                                <div class="card-body">
                                    <h5 class="card-title">{{ $itinerary->title }}</h5>
                                    <p class="card-text text-muted">
                                        <i class="bi bi-geo-alt"></i> {{ $itinerary->location }}, {{ $itinerary->country }}
                                    </p>
                                    <p class="card-text">{{ Str::limit($itinerary->description, 100) }}</p>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="h5 mb-0">${{ number_format($itinerary->price) }}</span>
                                        <a href="{{ route('itineraries.show', $itinerary) }}" class="btn btn-warning">View Details</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @else
                    <div class="col-12 text-center">
                        <p>No featured itineraries available at the moment.</p>
                    </div>
                @endisset
            </div>
            <div class="text-center mt-5">
                <a href="{{ route('itineraries.index') }}" class="btn btn-outline-warning">View All Adventures</a>
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
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h3 class="h5 mb-3">Trusted Experience</h3>
                        <p class="text-muted">Our itineraries are crafted by experienced travelers who know the best local spots and hidden gems.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="display-4 text-warning mb-3">
                            <i class="bi bi-heart"></i>
                        </div>
                        <h3 class="h5 mb-3">Personalized Service</h3>
                        <p class="text-muted">We tailor each journey to your preferences, ensuring a unique and memorable experience.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="text-center">
                        <div class="display-4 text-warning mb-3">
                            <i class="bi bi-globe"></i>
                        </div>
                        <h3 class="h5 mb-3">Global Network</h3>
                        <p class="text-muted">Access to exclusive experiences and local connections worldwide.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Featured Travellers -->
    <section class="travela-section bg-light">
        <div class="container">
            <h2 class="text-center mb-5">Featured Travellers</h2>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <img src="{{ asset('images/traveler1.jpg') }}" alt="Traveler" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                            <h5 class="card-title">Sarah Johnson</h5>
                            <p class="text-muted">Adventure Enthusiast</p>
                            <p class="card-text">"Travela helped me discover the most amazing hidden gems in Tanzania. The safari experience was beyond my expectations!"</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <img src="{{ asset('images/traveler2.jpg') }}" alt="Traveler" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                            <h5 class="card-title">Michael Chen</h5>
                            <p class="text-muted">Cultural Explorer</p>
                            <p class="card-text">"The attention to detail and local experiences made my trip to Morocco unforgettable. Highly recommended!"</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body text-center">
                            <img src="{{ asset('images/traveler3.jpg') }}" alt="Traveler" class="rounded-circle mb-3" style="width: 100px; height: 100px; object-fit: cover;">
                            <h5 class="card-title">Emma Rodriguez</h5>
                            <p class="text-muted">Luxury Traveler</p>
                            <p class="card-text">"From the moment I booked to the end of my journey, everything was perfect. The luxury accommodations were outstanding."</p>
                        </div>
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
                                    How do I book a trip?
                                </button>
                            </h3>
                            <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Booking a trip is easy! Simply browse our itineraries, select your preferred dates, and follow the booking process. Our team will guide you through every step.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                    What's included in the price?
                                </button>
                            </h3>
                            <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Each itinerary clearly lists what's included and excluded. Typically, we include accommodation, transportation, activities, and some meals. Check the specific itinerary for details.
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item">
                            <h3 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                    Can I customize my itinerary?
                                </button>
                            </h3>
                            <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                                <div class="accordion-body">
                                    Yes! We offer flexible itineraries that can be customized to your preferences. Contact our team to discuss your specific requirements.
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
