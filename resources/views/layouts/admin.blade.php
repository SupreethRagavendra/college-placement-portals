<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin - College Placement Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar { min-height: 100vh; background: linear-gradient(160deg, #6a6ef0 0%, #7a58c9 50%, #5f4aa8 100%); }
        .sidebar .nav-link { color: rgba(255, 255, 255, 0.9); padding: 12px 20px; border-radius: 8px; margin: 5px 0; transition: transform .2s ease, background-color .2s ease, color .2s ease; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background-color: rgba(255,255,255,.15); transform: translateX(4px); }
        .main-content { background-color: #f8f9fa; min-height: 100vh; }
        .card { border: none; box-shadow: 0 8px 20px rgba(31, 38, 135, 0.08); }
    </style>
    @stack('head')
    @yield('styles')
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js" defer></script>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="p-3">
                        <h4 class="text-white mb-4"><i class="fas fa-graduation-cap me-2"></i>Admin Panel</h4>
                        <nav class="nav flex-column">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
                            <a class="nav-link {{ request()->is('admin/students/pending') ? 'active' : '' }}" href="{{ route('admin.pending-students') }}"><i class="fas fa-clock me-2"></i>Pending Students</a>
                            <a class="nav-link {{ request()->is('admin/students/approved') ? 'active' : '' }}" href="{{ route('admin.approved-students') }}"><i class="fas fa-check-circle me-2"></i>Approved Students</a>
                            <a class="nav-link {{ request()->is('admin/students/rejected') ? 'active' : '' }}" href="{{ route('admin.rejected-students') }}"><i class="fas fa-times-circle me-2"></i>Rejected Students</a>
                            <hr class="text-white-50">
                            <a class="nav-link {{ request()->is('admin/assessments*') ? 'active' : '' }}" href="{{ route('admin.assessments.index') }}"><i class="fas fa-clipboard-list me-2"></i>Assessments</a>
                            <a class="nav-link {{ request()->is('admin/questions*') ? 'active' : '' }}" href="{{ route('admin.questions.index') }}"><i class="fas fa-question-circle me-2"></i>Questions</a>
                            <a class="nav-link {{ request()->routeIs('admin.reports.student-performance') ? 'active' : '' }}" href="{{ route('admin.reports.student-performance') }}"><i class="fas fa-user-graduate me-2"></i>Student Progress</a>
                            <a class="nav-link {{ request()->is('admin/reports*') && !request()->routeIs('admin.reports.student-performance') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}"><i class="fas fa-chart-line me-2"></i>Reports</a>
                            <hr class="text-white-50">
                            <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                        </nav>
                    </div>
                </div>
            </div>
            <div class="col-md-9 col-lg-10 px-0">
                <div class="main-content">
                    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                        <div class="container-fluid">
                            <h5 class="navbar-brand mb-0">@yield('title','Admin')</h5>
                            <div class="navbar-nav ms-auto"><span class="navbar-text me-3">Welcome, {{ Auth::user()->name ?? 'Admin' }}!</span></div>
                        </div>
                    </nav>
                    <div class="container-fluid p-4">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
    @stack('scripts')
    @yield('scripts')
</body>
</html>


