<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>College Placement Training Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .feature-card {
            transition: transform 0.3s ease;
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .btn-custom {
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        .section-padding {
            padding: 80px 0;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top" style="background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="#">
                <img src="{{ asset('css/logo1-removebg-preview.png') }}" alt="College Logo" style="height: 40px; margin-right: 10px;">
                College Placement Portal
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="#home">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Student Signup</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('login') }}">Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <h1 class="display-4 fw-bold mb-4">Welcome to College Placement Training Portal</h1>
                    <p class="lead mb-4">Empowering students with the skills and knowledge needed to excel in their career journey. Join thousands of successful graduates who found their dream jobs through our comprehensive training program.</p>
                    <div class="d-flex gap-3">
                        <a href="{{ route('register') }}" class="btn btn-light btn-custom">
                            <i class="fas fa-user-plus me-2"></i>REGISTER AS STUDENT
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-outline-light btn-custom">
                            <i class="fas fa-sign-in-alt me-2"></i>LOGIN
                        </a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="text-center">
                        <i class="fas fa-graduation-cap" style="font-size: 15rem; opacity: 0.3;"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="about" class="section-padding bg-light">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="display-5 fw-bold">Why Choose Us?</h2>
                <p class="lead text-muted">Comprehensive training and placement assistance</p>
            </div>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="text-center mb-3">
                            <i class="fas fa-laptop-code fa-3x text-primary"></i>
                        </div>
                        <h4 class="text-center mb-3">Technical Training</h4>
                        <p class="text-muted text-center">Comprehensive technical skill development programs tailored to industry requirements.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="text-center mb-3">
                            <i class="fas fa-users fa-3x text-success"></i>
                        </div>
                        <h4 class="text-center mb-3">Soft Skills</h4>
                        <p class="text-muted text-center">Communication, teamwork, and professional development workshops.</p>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card feature-card h-100 p-4">
                        <div class="text-center mb-3">
                            <i class="fas fa-briefcase fa-3x text-warning"></i>
                        </div>
                        <h4 class="text-center mb-3">Placement Support</h4>
                        <p class="text-muted text-center">Dedicated placement assistance and career guidance from industry experts.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="section-padding">
        <div class="container">
            <div class="row text-center">
                <div class="col-md-4 mb-4">
                    <h2 class="display-4 fw-bold text-primary">500+</h2>
                    <p class="lead">Students Trained</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h2 class="display-4 fw-bold text-success">100+</h2>
                    <p class="lead">Company Partnerships</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h2 class="display-4 fw-bold text-warning">95%</h2>
                    <p class="lead">Placement Rate</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-graduation-cap me-2"></i>College Placement Portal</h5>
                    <p class="text-muted">Empowering students for successful careers</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="mb-0">&copy; {{ date('Y') }} College Placement Portal. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

