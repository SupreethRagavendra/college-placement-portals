<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>KIT Coimbatore - Placement Portal</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- CSS -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
        @vite(['resources/css/app.css'])

            <style>
            body {
                font-family: 'Figtree', sans-serif;
                background: linear-gradient(135deg, #faf5ff 0%, #fff7ed 100%);
                min-height: 100vh;
            }

            .hero-section {
                background: linear-gradient(135deg, #7e22ce 0%, #581c87 100%);
                color: white;
                padding: 4rem 0 5rem;
                position: relative;
                overflow: hidden;
            }

            .hero-section::before {
                content: '';
                position: absolute;
                top: 0;
                right: 0;
                width: 50%;
                height: 100%;
                background-image: 
                    radial-gradient(circle at 80% 20%, rgba(249, 115, 22, 0.2) 0%, transparent 50%),
                    radial-gradient(circle at 60% 80%, rgba(249, 115, 22, 0.15) 0%, transparent 50%);
                z-index: 0;
            }

            .hero-content {
                position: relative;
                z-index: 1;
            }

            .feature-card {
                background: white;
                border-radius: 20px;
                padding: 2rem;
                transition: all 0.3s ease;
                border: 2px solid transparent;
                height: 100%;
            }

            .feature-card:hover {
                transform: translateY(-10px);
                border-color: #f97316;
                box-shadow: 0 20px 40px rgba(249, 115, 22, 0.2);
            }

            .feature-icon {
                width: 70px;
                height: 70px;
                border-radius: 50%;
                background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
                display: flex;
                align-items: center;
                justify-content: center;
                margin-bottom: 1.5rem;
                font-size: 2rem;
                color: white;
            }

            .stats-card {
                background: white;
                border-radius: 20px;
                padding: 2rem;
                text-align: center;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
            }

            .stats-number {
                font-size: 3rem;
                font-weight: 700;
                background: linear-gradient(135deg, #f97316 0%, #7e22ce 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }

            .kit-btn-primary {
                background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
                color: white;
                padding: 14px 35px;
                border-radius: 30px;
                border: none;
                font-weight: 600;
                font-size: 1.1rem;
                transition: all 0.3s ease;
                display: inline-block;
                text-decoration: none;
            }

            .kit-btn-primary:hover {
                transform: scale(1.05);
                box-shadow: 0 15px 35px rgba(249, 115, 22, 0.3);
                color: white;
            }

            .kit-btn-outline {
                background: transparent;
                color: white;
                padding: 14px 35px;
                border-radius: 30px;
                border: 2px solid white;
                font-weight: 600;
                font-size: 1.1rem;
                transition: all 0.3s ease;
                display: inline-block;
                text-decoration: none;
            }

            .kit-btn-outline:hover {
                background: white;
                color: #7e22ce;
                transform: scale(1.05);
            }

            .navbar-custom {
                background: rgba(126, 34, 206, 0.95);
                backdrop-filter: blur(10px);
                box-shadow: 0 2px 10px rgba(126, 34, 206, 0.3);
            }

            .logo-ring {
                width: 80px;
                height: 80px;
                border-radius: 50%;
                border: 3px solid white;
                padding: 5px;
                background: white;
            }

            @keyframes float {
                0%, 100% { transform: translateY(0); }
                50% { transform: translateY(-20px); }
            }

            .floating {
                animation: float 3s ease-in-out infinite;
            }
            </style>
    </head>
    <body>
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark navbar-custom fixed-top">
            <div class="container">
                <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
                    <img src="{{ asset('css/logo1-removebg-preview.png') }}" alt="KIT College Logo" style="height: 60px; margin-right: 15px;">
                    <div class="d-flex flex-column">
                        <span style="font-weight: 700; font-size: 1.2rem; line-height: 1.2;">KIT COIMBATORE</span>
                        <small style="font-size: 0.75rem; opacity: 0.9;">Excellence Beyond Expectation</small>
                    </div>
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto">
            @if (Route::has('login'))
                    @auth
                                <li class="nav-item">
                                    <a href="{{ url('/dashboard') }}" class="nav-link">
                                        <i class="fas fa-tachometer-alt me-1"></i> Dashboard
                                    </a>
                                </li>
                    @else
                                <li class="nav-item">
                                    <a href="{{ route('login') }}" class="nav-link">
                                        <i class="fas fa-sign-in-alt me-1"></i> Log in
                                    </a>
                                </li>
                        @if (Route::has('register'))
                                    <li class="nav-item ms-2">
                                        <a href="{{ route('register') }}" class="kit-btn-primary" style="padding: 10px 25px; font-size: 1rem;">
                                            <i class="fas fa-user-plus me-1"></i> Register
                                        </a>
                                    </li>
                                @endif
                            @endauth
                        @endif
                    </ul>
                </div>
            </div>
                </nav>

        <!-- Hero Section -->
        <section class="hero-section">
            <div class="container hero-content">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <h1 class="display-3 fw-bold mb-4">Welcome to<br><span style="color: #fbbf24;">KIT Placement Portal</span></h1>
                        <p class="lead mb-4" style="font-size: 1.2rem; opacity: 0.95;">Empowering students with world-class placement opportunities at Kalaignar Karunanidhi Institute of Technology.</p>
                        <div class="d-flex gap-3 flex-wrap">
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="kit-btn-primary">
                                    <i class="fas fa-rocket me-2"></i> Get Started
                                </a>
                            @endif
                            <a href="#features" class="kit-btn-outline">
                                <i class="fas fa-arrow-down me-2"></i> Learn More
                            </a>
                        </div>
                    </div>
                    <div class="col-lg-6 text-center mt-5 mt-lg-0">
                        <div class="floating">
                            <img src="{{ asset('css/logo1-removebg-preview.png') }}" alt="KIT Logo" class="logo-ring" style="width: 200px; height: 200px;">
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Stats Section -->
        <section class="py-5" style="margin-top: -50px; position: relative; z-index: 10;">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number">500+</div>
                            <p class="text-muted mb-0" style="font-weight: 600;">Students Placed</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number">100+</div>
                            <p class="text-muted mb-0" style="font-weight: 600;">Top Companies</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="stats-card">
                            <div class="stats-number">12 LPA</div>
                            <p class="text-muted mb-0" style="font-weight: 600;">Highest Package</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="py-5">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="display-5 fw-bold mb-3">Why Choose Our Portal?</h2>
                    <p class="lead text-muted">Everything you need for a successful placement journey</p>
                </div>
                <div class="row g-4">
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Assessment System</h4>
                            <p class="text-muted">Practice with real-time assessments and track your progress effectively.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Analytics Dashboard</h4>
                            <p class="text-muted">Get detailed insights into your performance with advanced analytics.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-building"></i>
                            </div>
                            <h4 class="fw-bold mb-3">Company Drive</h4>
                            <p class="text-muted">Stay updated with latest placement drives and opportunities.</p>
                        </div>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="fas fa-robot"></i>
                            </div>
                            <h4 class="fw-bold mb-3">AI Assistant</h4>
                            <p class="text-muted">Get personalized guidance with our intelligent AI chatbot.</p>
                        </div>
                    </div>
                </div>
        </div>
        </section>

        <!-- CTA Section -->
        <section class="py-5" style="background: linear-gradient(135deg, #7e22ce 0%, #581c87 100%);">
            <div class="container text-center text-white">
                <h2 class="display-5 fw-bold mb-3">Ready to Begin Your Journey?</h2>
                <p class="lead mb-4" style="opacity: 0.95;">Join thousands of students who have successfully placed through our portal.</p>
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="kit-btn-primary" style="background: white; color: #7e22ce;">
                        <i class="fas fa-user-graduate me-2"></i> Register Now
                    </a>
        @endif
            </div>
        </section>

        <!-- Footer -->
        <footer style="background: linear-gradient(135deg, #581c87 0%, #7e22ce 100%); color: white; padding: 3rem 0 1rem;">
            <div class="container">
                <div class="row">
                    <div class="col-md-6 mb-4 mb-md-0">
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ asset('css/logo1-removebg-preview.png') }}" alt="KIT College Logo" style="height: 60px; margin-right: 15px; filter: brightness(0) invert(1);">
                            <div>
                                <h5 class="mb-0" style="font-weight: 700;">KIT COIMBATORE</h5>
                                <small style="opacity: 0.9;">Excellence Beyond Expectation</small>
                            </div>
                        </div>
                        <p style="opacity: 0.9; font-size: 0.9rem;">
                            Kalaignar Karunanidhi Institute of Technology<br>
                            Empowering students with world-class placement opportunities.
                        </p>
                    </div>
                    <div class="col-md-3 mb-4 mb-md-0">
                        <h6 style="font-weight: 600; margin-bottom: 1rem;">Quick Links</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="https://kitcbe.com/" target="_blank" class="text-white text-decoration-none" style="opacity: 0.9;"><i class="fas fa-arrow-right me-2"></i>About KIT</a></li>
                            <li class="mb-2"><a href="#" class="text-white text-decoration-none" style="opacity: 0.9;"><i class="fas fa-arrow-right me-2"></i>Placement Cell</a></li>
                            <li class="mb-2"><a href="#" class="text-white text-decoration-none" style="opacity: 0.9;"><i class="fas fa-arrow-right me-2"></i>Contact Us</a></li>
                        </ul>
                    </div>
                    <div class="col-md-3">
                        <h6 style="font-weight: 600; margin-bottom: 1rem;">Connect With Us</h6>
                        <div class="d-flex gap-3">
                            <a href="#" class="text-white" style="font-size: 1.5rem; transition: all 0.3s;"><i class="fab fa-facebook"></i></a>
                            <a href="#" class="text-white" style="font-size: 1.5rem; transition: all 0.3s;"><i class="fab fa-twitter"></i></a>
                            <a href="#" class="text-white" style="font-size: 1.5rem; transition: all 0.3s;"><i class="fab fa-linkedin"></i></a>
                            <a href="#" class="text-white" style="font-size: 1.5rem; transition: all 0.3s;"><i class="fab fa-instagram"></i></a>
                        </div>
                    </div>
                </div>
                <hr style="border-color: rgba(255, 255, 255, 0.2); margin: 2rem 0 1rem;">
                <div class="text-center" style="opacity: 0.9;">
                    <small>&copy; {{ date('Y') }} KIT Coimbatore. All rights reserved. | Designed with <i class="fas fa-heart" style="color: #f97316;"></i> for students</small>
                </div>
            </div>
        </footer>

        <!-- JavaScript -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        </script>
    </body>
</html>
