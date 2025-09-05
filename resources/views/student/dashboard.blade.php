<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - College Placement Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 5px 0;
            transition: all 0.3s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.1);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .progress-card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
        }
        .achievement-badge {
            background: linear-gradient(45deg, #ffd700, #ffed4e);
            color: #333;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 px-0">
                <div class="sidebar">
                    <div class="p-3">
                        <h4 class="text-white mb-4">
                            <i class="fas fa-user-graduate me-2"></i>
                            Student Portal
                        </h4>
                        <nav class="nav flex-column">
                            <a class="nav-link active" href="#dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                            <a class="nav-link" href="#profile">
                                <i class="fas fa-user me-2"></i>Profile
                            </a>
                            <a class="nav-link" href="#courses">
                                <i class="fas fa-book me-2"></i>Courses
                            </a>
                            <a class="nav-link" href="#assignments">
                                <i class="fas fa-tasks me-2"></i>Assignments
                            </a>
                            <a class="nav-link" href="#placements">
                                <i class="fas fa-briefcase me-2"></i>Placements
                            </a>
                            <a class="nav-link" href="#interviews">
                                <i class="fas fa-calendar me-2"></i>Interviews
                            </a>
                            <hr class="text-white-50">
                            <a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 px-0">
                <div class="main-content">
                    <!-- Top Navigation -->
                    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
                        <div class="container-fluid">
                            <h5 class="navbar-brand mb-0">Student Dashboard</h5>
                            <div class="navbar-nav ms-auto">
                                <span class="navbar-text me-3">
                                    Welcome, {{ Auth::user()->name }}!
                                </span>
                                <div class="dropdown">
                                    <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-user-circle me-1"></i>
                                        {{ Auth::user()->name }}
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="#profile">Profile</a></li>
                                        <li><a class="dropdown-item" href="#settings">Settings</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li>
                                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                                Logout
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </nav>

                    <!-- Dashboard Content -->
                    <div class="container-fluid p-4">
                        <!-- Welcome Section -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="card progress-card">
                                    <div class="card-body">
                                        <div class="row align-items-center">
                                            <div class="col-md-8">
                                                <h4 class="card-title text-white mb-3">
                                                    <i class="fas fa-star me-2"></i>
                                                    Welcome back, {{ Auth::user()->name }}!
                                                </h4>
                                                <p class="card-text text-white-75 mb-3">
                                                    Continue your learning journey and track your progress towards your dream career.
                                                </p>
                                                <div class="progress mb-3" style="height: 8px;">
                                                    <div class="progress-bar bg-white" role="progressbar" style="width: 65%" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                                                </div>
                                                <small class="text-white-75">65% Course Completion</small>
                                            </div>
                                            <div class="col-md-4 text-center">
                                                <i class="fas fa-graduation-cap" style="font-size: 5rem; opacity: 0.3;"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stats Cards -->
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-book fa-2x text-primary mb-2"></i>
                                        <h5 class="card-title">8</h5>
                                        <p class="card-text text-muted">Courses Enrolled</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-tasks fa-2x text-success mb-2"></i>
                                        <h5 class="card-title">12</h5>
                                        <p class="card-text text-muted">Assignments Completed</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-calendar fa-2x text-warning mb-2"></i>
                                        <h5 class="card-title">3</h5>
                                        <p class="card-text text-muted">Upcoming Interviews</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-3">
                                <div class="card text-center">
                                    <div class="card-body">
                                        <i class="fas fa-trophy fa-2x text-info mb-2"></i>
                                        <h5 class="card-title">5</h5>
                                        <p class="card-text text-muted">Certificates Earned</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Main Content Row -->
                        <div class="row">
                            <!-- Recent Activities -->
                            <div class="col-md-8">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-clock me-2"></i>Recent Activities
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="list-group list-group-flush">
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Assignment Submitted</h6>
                                                    <p class="mb-1 text-muted">Web Development Project - Grade: A+</p>
                                                    <small class="text-muted">2 hours ago</small>
                                                </div>
                                                <span class="badge bg-success">Completed</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">New Course Available</h6>
                                                    <p class="mb-1 text-muted">Advanced JavaScript and React</p>
                                                    <small class="text-muted">1 day ago</small>
                                                </div>
                                                <span class="badge bg-primary">New</span>
                                            </div>
                                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <h6 class="mb-1">Interview Scheduled</h6>
                                                    <p class="mb-1 text-muted">Tech Corp - Software Developer Position</p>
                                                    <small class="text-muted">3 days ago</small>
                                                </div>
                                                <span class="badge bg-warning">Scheduled</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Quick Actions & Achievements -->
                            <div class="col-md-4">
                                <!-- Quick Actions -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-bolt me-2"></i>Quick Actions
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <button class="btn btn-primary">
                                                <i class="fas fa-play me-2"></i>Continue Learning
                                            </button>
                                            <button class="btn btn-success">
                                                <i class="fas fa-upload me-2"></i>Submit Assignment
                                            </button>
                                            <button class="btn btn-info">
                                                <i class="fas fa-calendar me-2"></i>View Schedule
                                            </button>
                                            <button class="btn btn-warning">
                                                <i class="fas fa-file-alt me-2"></i>Download Certificate
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Achievements -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-trophy me-2"></i>Achievements
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="achievement-badge px-2 py-1 rounded">
                                                <i class="fas fa-star me-1"></i>First Assignment
                                            </span>
                                            <span class="achievement-badge px-2 py-1 rounded">
                                                <i class="fas fa-fire me-1"></i>5 Day Streak
                                            </span>
                                            <span class="achievement-badge px-2 py-1 rounded">
                                                <i class="fas fa-medal me-1"></i>Top Performer
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
        @csrf
    </form>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
