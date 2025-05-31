<footer class="bg-white border-top py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="mb-3">Travela</h5>
                <p class="text-muted">Your trusted platform for discovering and sharing amazing travel experiences.</p>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="mb-3">Quick Links</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Home</a></li>
                    <li class="mb-2"><a href="{{ route('itineraries.index') }}" class="text-decoration-none text-muted">Browse Itineraries</a></li>
                    <li class="mb-2"><a href="{{ route('contact') }}" class="text-decoration-none text-muted">Contact Us</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h5 class="mb-3">Legal</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="{{ route('pages.terms') }}" class="text-decoration-none text-muted">Terms & Conditions</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Privacy Policy</a></li>
                    <li class="mb-2"><a href="#" class="text-decoration-none text-muted">Cookie Policy</a></li>
                </ul>
            </div>
        </div>
        <hr class="my-4">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0 text-muted">&copy; {{ date('Y') }} Travela. All rights reserved.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <div class="social-links">
                    <a href="#" class="text-muted me-3"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-muted me-3"><i class="bi bi-twitter"></i></a>
                    <a href="#" class="text-muted me-3"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-muted"><i class="bi bi-linkedin"></i></a>
                </div>
            </div>
        </div>
    </div>
</footer> 