@extends('layouts.admin')

@section('title', 'Reports & Analytics')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="mb-0">Reports & Analytics</h1>
            <p class="text-muted mb-0">Comprehensive analytics and performance insights</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.export') }}" class="btn btn-outline-primary">
                <i class="fas fa-download me-2"></i>Export CSV
            </a>
            <a href="{{ route('admin.reports.student-performance') }}" class="btn btn-primary">
                <i class="fas fa-user-graduate me-2"></i>Student Progress
            </a>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-users fa-2x text-primary mb-2"></i>
                    <h4 class="mb-1">{{ $overallStats['total_students'] }}</h4>
                    <p class="text-muted mb-0">Total Students</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-clipboard-list fa-2x text-info mb-2"></i>
                    <h4 class="mb-1">{{ $overallStats['total_assessments'] }}</h4>
                    <p class="text-muted mb-0">Total Assessments</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                    <h4 class="mb-1">{{ round($overallStats['average_score'], 1) }}%</h4>
                    <p class="text-muted mb-0">Average Score</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center border-0 shadow-sm">
                <div class="card-body">
                    <i class="fas fa-trophy fa-2x text-warning mb-2"></i>
                    <h4 class="mb-1">{{ $overallStats['total_attempts'] }}</h4>
                    <p class="text-muted mb-0">Total Attempts</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Assessment Performance Overview -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Assessment Performance Overview
                    </h5>
                </div>
                <div class="card-body">
                    @if($assessmentStats->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Assessment</th>
                                        <th style="width: 100px">Category</th>
                                        <th style="width: 100px">Questions</th>
                                        <th style="width: 100px">Attempts</th>
                                        <th style="width: 120px">Avg Score</th>
                                        <th style="width: 100px">Pass Rate</th>
                                        <th style="width: 100px">Avg Time</th>
                                        <th style="width: 120px" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($assessmentStats as $assessment)
                                        <tr>
                                            <td>
                                                <div>
                                                    <h6 class="mb-0">{{ $assessment['title'] }}</h6>
                                                    <small class="text-muted">ID: {{ $assessment['id'] }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $assessment['category'] === 'Aptitude' ? 'primary' : 'success' }}">
                                                    {{ $assessment['category'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info">{{ $assessment['total_questions'] }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">{{ $assessment['total_attempts'] }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress me-2" style="width: 60px; height: 8px;">
                                                        <div class="progress-bar bg-{{ $assessment['avg_percentage'] >= 70 ? 'success' : ($assessment['avg_percentage'] >= 50 ? 'warning' : 'danger') }}" 
                                                             style="width: {{ min($assessment['avg_percentage'], 100) }}%"></div>
                                                    </div>
                                                    <span class="fw-bold">{{ $assessment['avg_percentage'] }}%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $assessment['pass_rate'] >= 70 ? 'success' : ($assessment['pass_rate'] >= 50 ? 'warning' : 'danger') }}">
                                                    {{ $assessment['pass_rate'] }}%
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">{{ round($assessment['avg_time'] / 60, 1) }}m</span>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.reports.assessment-details', $assessment['id']) }}" 
                                                       class="btn btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('admin.assessments.questions', $assessment['id']) }}" 
                                                       class="btn btn-outline-info">
                                                        <i class="fas fa-question-circle"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-chart-bar fa-4x text-muted mb-3"></i>
                            <h5>No Assessment Data</h5>
                            <p class="text-muted">No assessments have been created or attempted yet.</p>
                            <a href="{{ route('admin.assessments.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>Create Assessment
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-tools me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.reports.student-performance') }}" class="btn btn-outline-primary">
                            <i class="fas fa-user-graduate me-2"></i>View Student Progress
                        </a>
                        <a href="{{ route('admin.reports.category-analysis') }}" class="btn btn-outline-info">
                            <i class="fas fa-chart-pie me-2"></i>Category Analysis
                        </a>
                        <a href="{{ route('admin.assessments.index') }}" class="btn btn-outline-success">
                            <i class="fas fa-clipboard-list me-2"></i>Manage Assessments
                        </a>
                        <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-warning">
                            <i class="fas fa-question-circle me-2"></i>Manage Questions
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-download me-2"></i>Export Options
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.reports.export', ['type' => 'all']) }}" class="btn btn-outline-primary">
                            <i class="fas fa-file-csv me-2"></i>Export All Results
                        </a>
                        <a href="{{ route('admin.reports.export', ['type' => 'student']) }}" class="btn btn-outline-info">
                            <i class="fas fa-users me-2"></i>Export Student Data
                        </a>
                        <a href="{{ route('admin.reports.export', ['type' => 'assessment']) }}" class="btn btn-outline-success">
                            <i class="fas fa-clipboard-list me-2"></i>Export Assessment Data
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.progress {
    border-radius: 10px;
    background-color: #e9ecef;
}

.progress-bar {
    border-radius: 10px;
    transition: width 0.3s ease;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>
@endsection
