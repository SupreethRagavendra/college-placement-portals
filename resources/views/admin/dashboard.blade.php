<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - College Placement Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(160deg, #6a6ef0 0%, #7a58c9 50%, #5f4aa8 100%);
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.9);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 5px 0;
            transition: transform 0.2s ease, background-color 0.2s ease, color 0.2s ease;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: white;
            background-color: rgba(255, 255, 255, 0.15);
            transform: translateX(4px);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            box-shadow: 0 8px 20px rgba(31, 38, 135, 0.08);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px rgba(31, 38, 135, 0.12);
        }
        .stat-card {
            background: linear-gradient(135deg, #6f7bf7 0%, #7a56d0 100%);
            color: white;
            position: relative;
            overflow: hidden;
        }
        .stat-card .icon-wrap {
            position: absolute;
            top: -12px;
            right: -12px;
            opacity: 0.15;
            font-size: 4.5rem;
        }
        .count-up {
            display: inline-block;
            min-width: 2ch;
        }
        .btn-quick {
            border-radius: 999px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .btn-quick:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 18px rgba(0,0,0,0.08);
        }
        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background: #e9ecef;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #6c757d;
        }
        .scrollable {
            max-height: 300px;
            overflow: auto;
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
                            <i class="fas fa-graduation-cap me-2"></i>
                            Admin Panel
                        </h4>
                        <nav class="nav flex-column">
                            <a class="nav-link active" href="#dashboard">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                            <a class="nav-link" href="{{ route('admin.pending-students') }}">
                                <i class="fas fa-clock me-2"></i>Pending Students
                            </a>
                            <a class="nav-link" href="{{ route('admin.approved-students') }}">
                                <i class="fas fa-check-circle me-2"></i>Approved Students
                            </a>
                            <a class="nav-link" href="{{ route('admin.rejected-students') }}">
                                <i class="fas fa-times-circle me-2"></i>Rejected Students
                            </a>
                            <hr class="text-white-50">
                            <a class="nav-link" href="{{ route('admin.assessments.index') }}">
                                <i class="fas fa-clipboard-list me-2"></i>Assessments
                            </a>
                            <a class="nav-link" href="{{ route('admin.questions.index') }}">
                                <i class="fas fa-question-circle me-2"></i>Questions
                            </a>
                            <a class="nav-link" href="{{ route('admin.reports.student-performance') }}">
                                <i class="fas fa-user-graduate me-2"></i>Student Progress
                            </a>
                            <a class="nav-link" href="{{ url('/admin/reports') }}">
                                <i class="fas fa-chart-line me-2"></i>Reports
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
                            <h5 class="navbar-brand mb-0">Admin Dashboard</h5>
                            <div class="navbar-nav ms-auto">
                                <span class="navbar-text me-3">
                                    Welcome, {{ Auth::user()->name }}!
                                </span>
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
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
                        <!-- Stats Cards -->
                        <div class="row mb-4 g-3">
                            <div class="col-6 col-md-3">
                                <div class="card stat-card">
                                    <div class="icon-wrap"><i class="fas fa-users"></i></div>
                                    <div class="card-body text-center">
                                        <div class="mb-1"><i class="fas fa-users"></i></div>
                                        <h3 class="card-title mb-1"><span class="count-up" data-count="{{ $stats['total_students'] }}">0</span></h3>
                                        <p class="card-text mb-0">Total Students</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="card stat-card" style="background: linear-gradient(135deg,#38c172 0%, #1f9d55 100%);">
                                    <div class="icon-wrap"><i class="fas fa-check-circle"></i></div>
                                    <div class="card-body text-center">
                                        <div class="mb-1"><i class="fas fa-check-circle"></i></div>
                                        <h3 class="card-title mb-1"><span class="count-up" data-count="{{ $stats['approved_students'] }}">0</span></h3>
                                        <p class="card-text mb-0">Approved Students</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="card stat-card" style="background: linear-gradient(135deg,#ffca3a 0%, #f4a600 100%);">
                                    <div class="icon-wrap"><i class="fas fa-clock"></i></div>
                                    <div class="card-body text-center">
                                        <div class="mb-1"><i class="fas fa-clock"></i></div>
                                        <h3 class="card-title mb-1"><span class="count-up" data-count="{{ $stats['pending_students'] }}">0</span></h3>
                                        <p class="card-text mb-0">Pending Approval</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="card stat-card" style="background: linear-gradient(135deg,#ef5753 0%, #e3342f 100%);">
                                    <div class="icon-wrap"><i class="fas fa-times-circle"></i></div>
                                    <div class="card-body text-center">
                                        <div class="mb-1"><i class="fas fa-times-circle"></i></div>
                                        <h3 class="card-title mb-1"><span class="count-up" data-count="{{ $stats['rejected_students'] }}">0</span></h3>
                                        <p class="card-text mb-0">Rejected Students</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Content & Assessment Stats -->
                        <div class="row mb-4 g-3">
                            <div class="col-6 col-md-3">
                                <div class="card stat-card" style="background: linear-gradient(135deg,#6366f1 0%, #4338ca 100%);">
                                    <div class="icon-wrap"><i class="fas fa-clipboard-list"></i></div>
                                    <div class="card-body text-center">
                                        <div class="mb-1"><i class="fas fa-clipboard-list"></i></div>
                                        <h3 class="card-title mb-1"><span class="count-up" data-count="{{ $stats['total_assessments'] ?? 0 }}">0</span></h3>
                                        <p class="card-text mb-0">Total Assessments</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="card stat-card" style="background: linear-gradient(135deg,#0ea5e9 0%, #0369a1 100%);">
                                    <div class="icon-wrap"><i class="fas fa-file-alt"></i></div>
                                    <div class="card-body text-center">
                                        <div class="mb-1"><i class="fas fa-file-alt"></i></div>
                                        <h3 class="card-title mb-1"><span class="count-up" data-count="{{ $stats['total_attempts'] ?? 0 }}">0</span></h3>
                                        <p class="card-text mb-0">Student Attempts</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="card stat-card" style="background: linear-gradient(135deg,#22c55e 0%, #15803d 100%);">
                                    <div class="icon-wrap"><i class="fas fa-star"></i></div>
                                    <div class="card-body text-center">
                                        <div class="mb-1"><i class="fas fa-star"></i></div>
                                        <h3 class="card-title mb-1">Avg Score: <span class="count-up" data-count="{{ $stats['average_score'] ?? 0 }}">0</span>%</h3>
                                        <p class="card-text mb-0">Across all student attempts</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Activities -->
                        <div class="row">
                            <div class="col-md-8">
                                <!-- Recent Assessments -->
                                <div class="card mb-4">
                                    <div class="card-header d-flex align-items-center justify-content-between">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-clipboard-list me-2"></i>Recent Assessments
                                        </h5>
                                        <a href="{{ route('admin.assessments.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                                    </div>
                                    <div class="card-body p-0">
                                        @if($recentAssessments->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-striped align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Assessment</th>
                                                            <th>Category</th>
                                                            <th>Questions</th>
                                                            <th>Attempts</th>
                                                            <th>Status</th>
                                                            <th class="text-end">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($recentAssessments as $assessment)
                                                            <tr>
                                                                <td>
                                                                    <div>
                                                                        <strong>{{ $assessment->title }}</strong>
                                                                        @if($assessment->description)
                                                                            <br><small class="text-muted">{{ Str::limit($assessment->description, 50) }}</small>
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                                <td><span class="badge bg-{{ $assessment->category === 'Aptitude' ? 'primary' : 'success' }}">{{ $assessment->category }}</span></td>
                                                                <td><span class="badge bg-info">{{ $assessment->questions_count }}</span></td>
                                                                <td><span class="badge bg-secondary">{{ $assessment->student_results_count }}</span></td>
                                                                <td>
                                                                    @if($assessment->is_active)
                                                                        <span class="badge bg-success">Active</span>
                                                                    @else
                                                                        <span class="badge bg-secondary">Inactive</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-end">
                                                                    <div class="btn-group btn-group-sm">
                                                                        <a href="{{ route('admin.assessments.questions', $assessment) }}" class="btn btn-outline-primary btn-sm">Questions</a>
                                                                        <a href="{{ route('admin.assessments.edit', $assessment) }}" class="btn btn-outline-secondary btn-sm">Edit</a>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        @else
                                            <div class="text-center py-4">
                                                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                                <h5>No Assessments Yet</h5>
                                                <p class="text-muted">Create your first assessment to get started!</p>
                                                <a href="{{ route('admin.assessments.create') }}" class="btn btn-primary">Create Assessment</a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header d-flex align-items-center justify-content-between">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-clock me-2"></i>Pending Student Approvals
                                        </h5>
                                        <span class="badge text-bg-warning">Pending: {{ $stats['pending_students'] }}</span>
                                    </div>
                                    <div class="card-body p-0">
                                        @if($pendingStudents->count() > 0)
                                            <div class="table-responsive">
                                                <table class="table table-striped align-middle mb-0">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th>Name</th>
                                                            <th>Email</th>
                                                            <th>Registered</th>
                                                            <th>Status</th>
                                                            <th class="text-end">Actions</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($pendingStudents->take(5) as $student)
                                                            <tr>
                                                                <td>{{ $student->name }}</td>
                                                                <td>{{ $student->email }}</td>
                                                                <td>{{ $student->created_at->diffForHumans() }}</td>
                                                                <td><span class="badge text-bg-warning">Pending</span></td>
                                                                <td class="text-end">
                                                                    <button class="btn btn-success btn-sm btn-quick" onclick="approveStudent({{ $student->id }})">
                                                                        <i class="fas fa-check me-1"></i>Approve
                                                                    </button>
                                                                    <button class="btn btn-danger btn-sm btn-quick" onclick="rejectStudent({{ $student->id }})">
                                                                        <i class="fas fa-times me-1"></i>Reject
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                            @if($pendingStudents->count() > 5)
                                                <div class="text-center p-3">
                                                    <a href="{{ route('admin.pending-students') }}" class="btn btn-outline-primary btn-quick">
                                                        View All Pending Students ({{ $pendingStudents->count() }})
                                                    </a>
                                                </div>
                                            @endif
                                        @else
                                            <div class="text-center py-4">
                                                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                                                <h5>No Pending Approvals</h5>
                                                <p class="text-muted">All students have been processed!</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-tasks me-2"></i>Quick Actions
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('admin.pending-students') }}" class="btn btn-warning btn-quick">
                                                <i class="fas fa-clock me-2"></i>Review Pending
                                            </a>
                                            <a href="{{ route('admin.approved-students') }}" class="btn btn-success btn-quick">
                                                <i class="fas fa-check-circle me-2"></i>View Approved
                                            </a>
                                            <a href="{{ route('admin.rejected-students') }}" class="btn btn-danger btn-quick">
                                                <i class="fas fa-times-circle me-2"></i>View Rejected
                                            </a>
                                            <hr class="my-2">
                                            <a href="{{ route('admin.assessments.create') }}" class="btn btn-primary btn-quick">
                                                <i class="fas fa-plus me-2"></i>Create Assessment
                                            </a>
                                            <a href="{{ route('admin.assessments.index') }}" class="btn btn-outline-primary btn-quick">
                                                <i class="fas fa-clipboard-list me-2"></i>Manage Assessments
                                            </a>
                                            <a href="{{ route('admin.questions.create') }}" class="btn btn-outline-success btn-quick">
                                                <i class="fas fa-question-circle me-2"></i>Add Questions
                                            </a>
                                            <a href="{{ route('admin.reports.student-performance') }}" class="btn btn-outline-info btn-quick">
                                                <i class="fas fa-user-graduate me-2"></i>Student Progress
                                            </a>
                                            <a href="{{ url('/admin/reports') }}" class="btn btn-outline-secondary btn-quick">
                                                <i class="fas fa-chart-line me-2"></i>View Reports
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <!-- Recent Approvals -->
                                <div class="card mt-3">
                                    <div class="card-header">
                                        <h5 class="card-title mb-0">
                                            <i class="fas fa-history me-2"></i>Recent Approvals
                                        </h5>
                                    </div>
                                    <div class="card-body scrollable">
                                        @if($recentApprovals->count() > 0)
                                            <div class="list-group list-group-flush">
                                                @foreach($recentApprovals as $student)
                                                    <div class="list-group-item px-0 d-flex align-items-center gap-3">
                                                        <div class="avatar">{{ strtoupper(substr($student->name,0,1)) }}</div>
                                                        <div>
                                                            <h6 class="mb-0">{{ $student->name }}</h6>
                                                            <small class="text-muted">Approved {{ $student->admin_approved_at->diffForHumans() }}</small>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <p class="text-muted text-center">No recent approvals</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="card mt-3">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="card-title mb-0"><i class="fas fa-chart-line me-2"></i>Reports Preview</h5>
                                        <a href="{{ url('/admin/reports') }}" class="btn btn-sm btn-outline-primary btn-quick">Open Full</a>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="miniChart" height="130"></canvas>
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Count-up animation
        document.querySelectorAll('.count-up').forEach(el => {
            const target = Number(el.getAttribute('data-count') || 0);
            const duration = 1000;
            const start = performance.now();
            const step = (now) => {
                const progress = Math.min(1, (now - start) / duration);
                const value = Math.floor(progress * target);
                el.textContent = value.toLocaleString();
                if (progress < 1) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        });

        function approveStudent(studentId) {
            if (confirm('Are you sure you want to approve this student?')) {
                fetch(`/admin/students/${studentId}/approve`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                })
                .then(() => location.reload())
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred while approving the student');
                });
            }
        }

        function rejectStudent(studentId) {
            const reason = prompt('Please provide a reason for rejection:');
            if (reason && reason.trim()) {
                if (confirm('Are you sure you want to reject this student? This action cannot be undone.')) {
                    fetch(`/admin/students/${studentId}/reject`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        },
                    })
                    .then(() => location.reload())
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while rejecting the student');
                    });
                }
            }
        }

        // Mini chart preview
        const miniCtx = document.getElementById('miniChart');
        if (miniCtx) {
            new Chart(miniCtx, {
                type: 'bar',
                data: {
                    labels: ['Approved', 'Pending', 'Rejected'],
                    datasets: [{
                        label: 'Students',
                        data: [
                            {{ $stats['approved_students'] }},
                            {{ $stats['pending_students'] }},
                            {{ $stats['rejected_students'] }}
                        ],
                        backgroundColor: ['#1f9d55', '#f4a600', '#e3342f']
                    }]
                },
                options: {
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } },
                    plugins: { legend: { display: false } },
                }
            });
        }
    </script>
</body>
</html>
