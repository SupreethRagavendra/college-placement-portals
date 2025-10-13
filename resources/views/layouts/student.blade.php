<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Student') - College Placement Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { 
            min-height: 100vh; 
            background: linear-gradient(160deg, #667eea 0%, #764ba2 50%, #5f4aa8 100%); 
        }
        .sidebar .nav-link { 
            color: rgba(255, 255, 255, 0.9); 
            padding: 12px 20px; 
            border-radius: 8px; 
            margin: 5px 0; 
            transition: transform .2s ease, background-color .2s ease, color .2s ease; 
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { 
            color: #fff; 
            background-color: rgba(255,255,255,.15); 
            transform: translateX(4px); 
        }
        .main-content { 
            background-color: #f8f9fa; 
            min-height: 100vh; 
        }
        .card { 
            border: none; 
            box-shadow: 0 8px 20px rgba(31, 38, 135, 0.08); 
        }
    </style>
    @stack('head')
    @yield('styles')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="p-3">
                        <div class="d-flex align-items-center mb-4">
                            <img src="{{ asset('css/logo1-removebg-preview.png') }}" alt="College Logo" style="height: 50px; width: 50px; object-fit: contain; background: white; border-radius: 50%; padding: 5px; margin-right: 12px;">
                            <h5 class="text-white mb-0">Student Portal</h5>
                        </div>
                        <nav class="nav flex-column">
                            <a class="nav-link {{ request()->routeIs('student.dashboard') ? 'active' : '' }}" href="{{ route('student.dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                            <a class="nav-link {{ request()->routeIs('student.assessments.*') ? 'active' : '' }}" href="{{ route('student.assessments.index') }}">
                                <i class="fas fa-clipboard-list me-2"></i>Assessments
                            </a>
                            <a class="nav-link {{ request()->routeIs('student.assessment.history') ? 'active' : '' }}" href="{{ route('student.assessment.history') }}">
                                <i class="fas fa-history me-2"></i>Test History
                            </a>
                            <a class="nav-link {{ request()->routeIs('student.results.*') ? 'active' : '' }}" href="{{ route('student.results.index') }}">
                                <i class="fas fa-chart-bar me-2"></i>Results
                            </a>
                            <a class="nav-link {{ request()->routeIs('student.categories') ? 'active' : '' }}" href="{{ route('student.categories') }}">
                                <i class="fas fa-tags me-2"></i>Categories
                            </a>
                            <a class="nav-link {{ request()->routeIs('student.assessments.analytics') ? 'active' : '' }}" href="{{ route('student.assessments.analytics') }}">
                                <i class="fas fa-chart-line me-2"></i>Analytics
                            </a>
                            <a class="nav-link {{ request()->routeIs('profile.edit') ? 'active' : '' }}" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                            <hr class="text-white-50">
                            <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </nav>
                    </div>
                </div>
            </div>
            <div class="col-md-9 col-lg-10 px-0">
                <div class="main-content">
                    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                        <div class="container-fluid">
                            <h5 class="navbar-brand mb-0">@yield('title', 'Student')</h5>
                            <div class="navbar-nav ms-auto">
                                <span class="navbar-text me-3">Welcome, {{ Auth::user()->name ?? 'Student' }}!</span>
                            </div>
                        </div>
                    </nav>
                    <div class="container-fluid p-4">
                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        @if(session('status'))
                            <div class="alert alert-info alert-dismissible fade show" role="alert">
                                <i class="fas fa-info-circle me-2"></i>{{ session('status') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif
                        
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
    
    <!-- Include Student Chatbot Component -->
    @include('components.student-chatbot')
    
    <!-- Chatbot Debug Helper (only in local environment) -->
    @if(app()->environment('local'))
        <script src="{{ asset('js/chatbot-debug.js') }}"></script>
    @endif
    
    @stack('scripts')
    @yield('scripts')
</body>
</html>
