@extends('layouts.admin')

@section('title', 'Student Performance Details - ' . $student->name)

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-2">Student Performance Details</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.reports.student-performance') }}">Students</a></li>
                            <li class="breadcrumb-item active">{{ $student->name }}</li>
                        </ol>
                    </nav>
                </div>
                <div>
                    <button onclick="window.print()" class="btn btn-secondary">
                        <i class="fas fa-print"></i> Print Report
                    </button>
                    <a href="{{ route('admin.reports.student-performance') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Student Info Card -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-auto">
                            <div class="avatar-lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <span class="h2 mb-0">{{ substr($student->name, 0, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-md">
                            <h4 class="mb-1">{{ $student->name }}</h4>
                            <p class="text-muted mb-2">
                                <i class="fas fa-envelope"></i> {{ $student->email }}
                                <span class="mx-2">|</span>
                                <i class="fas fa-id-badge"></i> Student ID: {{ $student->id }}
                            </p>
                            <div class="d-flex gap-3">
                                <span class="badge bg-info">{{ $overallStats['total_assessments'] }} Assessments Taken</span>
                                <span class="badge bg-success">{{ number_format($overallStats['pass_rate'], 1) }}% Pass Rate</span>
                                <span class="badge bg-primary">{{ number_format($overallStats['average_score'], 1) }}% Avg Score</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Overall Statistics -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Assessments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $overallStats['total_assessments'] }}</div>
                            <small class="text-muted">{{ $overallStats['unique_assessments'] }} unique</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Average Score</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($overallStats['average_score'], 1) }}%</div>
                            <small class="text-muted">Best: {{ number_format($overallStats['best_score'], 1) }}%</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Time Spent</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                @php
                                    $totalSeconds = $overallStats['total_time_spent'];
                                    $hours = floor($totalSeconds / 3600);
                                    $minutes = floor(($totalSeconds % 3600) / 60);
                                @endphp
                                {{ $hours }}h {{ $minutes }}m
                            </div>
                            <small class="text-muted">Avg: {{ round($overallStats['average_time'] / 60) }}m</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pass Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($overallStats['pass_rate'], 1) }}%</div>
                            <small class="text-muted">
                                @php
                                    $passed = collect($assessmentHistory)->where('score_percentage', '>=', 50)->count();
                                @endphp
                                {{ $passed }} passed
                            </small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-trophy fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <!-- Performance Trend Chart -->
        <div class="col-lg-8 mb-3">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Performance Trend (Last 10 Assessments)</h6>
                </div>
                <div class="card-body">
                    <canvas id="performanceTrendChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Grade Distribution -->
        <div class="col-lg-4 mb-3">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Grade Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="gradeChart" height="200"></canvas>
                    <div class="mt-3">
                        @foreach($gradeDistribution as $grade => $count)
                        <div class="d-flex justify-content-between mb-1">
                            <span class="badge bg-{{ $grade == 'A' ? 'success' : ($grade == 'F' ? 'danger' : 'warning') }}">Grade {{ $grade }}</span>
                            <span>{{ $count }} assessment(s)</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category & Difficulty Performance -->
    <div class="row mb-4">
        <!-- Category Performance -->
        <div class="col-lg-6 mb-3">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Category-wise Performance</h6>
                </div>
                <div class="card-body">
                    <canvas id="categoryChart" height="150"></canvas>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Attempts</th>
                                    <th>Avg Score</th>
                                    <th>Pass Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($categoryPerformance as $cat)
                                <tr>
                                    <td>{{ $cat['category'] ?? 'N/A' }}</td>
                                    <td>{{ $cat['attempts'] }}</td>
                                    <td>{{ number_format($cat['average_score'], 1) }}%</td>
                                    <td>
                                        <span class="badge bg-{{ $cat['pass_rate'] >= 60 ? 'success' : 'warning' }}">
                                            {{ number_format($cat['pass_rate'], 1) }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Difficulty Performance -->
        <div class="col-lg-6 mb-3">
            <div class="card shadow">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Difficulty-wise Performance</h6>
                </div>
                <div class="card-body">
                    <canvas id="difficultyChart" height="150"></canvas>
                    <div class="table-responsive mt-3">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Difficulty</th>
                                    <th>Attempts</th>
                                    <th>Avg Score</th>
                                    <th>Pass Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($difficultyPerformance as $diff)
                                <tr>
                                    <td>
                                        <span class="badge bg-{{ strtolower($diff['difficulty']) == 'easy' ? 'success' : (strtolower($diff['difficulty']) == 'hard' ? 'danger' : 'warning') }}">
                                            {{ $diff['difficulty'] }}
                                        </span>
                                    </td>
                                    <td>{{ $diff['attempts'] }}</td>
                                    <td>{{ $diff['average_score'] }}%</td>
                                    <td>{{ $diff['pass_rate'] }}%</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Assessment History Table -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Complete Assessment History</h6>
                    <span class="badge bg-secondary">{{ $assessmentHistory->count() }} Total Attempts</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover" id="assessmentHistoryTable">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Assessment</th>
                                    <th>Category</th>
                                    <th>Difficulty</th>
                                    <th>Score</th>
                                    <th>Grade</th>
                                    <th>Time Taken</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($assessmentHistory as $attempt)
                                <tr>
                                    <td>{{ $attempt->submitted_at ? $attempt->submitted_at->format('M d, Y') : 'N/A' }}</td>
                                    <td>{{ $attempt->assessment->name ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-info">{{ $attempt->assessment->category ?? 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ ($attempt->assessment->difficulty_level ?? '') == 'easy' ? 'success' : (($attempt->assessment->difficulty_level ?? '') == 'hard' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($attempt->assessment->difficulty_level ?? 'N/A') }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $attempt->score }}/{{ $attempt->total_questions }}</strong>
                                        <br>
                                        <small class="text-muted">{{ number_format($attempt->score_percentage, 1) }}%</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $attempt->grade == 'A' ? 'success' : ($attempt->grade == 'F' ? 'danger' : 'warning') }}">
                                            {{ $attempt->grade }}
                                        </span>
                                    </td>
                                    <td>
                                        @php
                                            $minutes = floor($attempt->time_taken / 60);
                                            $seconds = $attempt->time_taken % 60;
                                        @endphp
                                        {{ $minutes }}m {{ $seconds }}s
                                    </td>
                                    <td>
                                        @if($attempt->score_percentage >= 50)
                                            <span class="badge bg-success">Passed</span>
                                        @else
                                            <span class="badge bg-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.reports.assessment-details', $attempt->assessment_id) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View Assessment Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .border-left-primary {
        border-left: 4px solid #4e73df !important;
    }
    .border-left-success {
        border-left: 4px solid #1cc88a !important;
    }
    .border-left-info {
        border-left: 4px solid #36b9cc !important;
    }
    .border-left-warning {
        border-left: 4px solid #f6c23e !important;
    }
    .text-gray-800 {
        color: #5a5c69 !important;
    }
    .text-gray-300 {
        color: #dddfeb !important;
    }
    @media print {
        .btn, nav {
            display: none !important;
        }
    }
</style>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#assessmentHistoryTable').DataTable({
        order: [[0, 'desc']],
        pageLength: 10,
        responsive: true
    });

    // Performance Trend Chart
    const trendData = @json($recentTrend);
    new Chart(document.getElementById('performanceTrendChart'), {
        type: 'line',
        data: {
            labels: trendData.map(d => d.date),
            datasets: [{
                label: 'Score %',
                data: trendData.map(d => d.score),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });

    // Grade Distribution Chart
    const gradeData = @json($gradeDistribution);
    new Chart(document.getElementById('gradeChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(gradeData),
            datasets: [{
                data: Object.values(gradeData),
                backgroundColor: [
                    'rgba(40, 167, 69, 0.8)',
                    'rgba(255, 193, 7, 0.8)',
                    'rgba(253, 126, 20, 0.8)',
                    'rgba(220, 53, 69, 0.8)',
                    'rgba(220, 53, 69, 1)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });

    // Category Performance Chart
    const categoryData = @json($categoryPerformance);
    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: categoryData.map(c => c.category || 'N/A'),
            datasets: [{
                label: 'Average Score %',
                data: categoryData.map(c => c.average_score),
                backgroundColor: 'rgba(54, 162, 235, 0.8)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });

    // Difficulty Performance Chart
    const difficultyData = @json($difficultyPerformance);
    new Chart(document.getElementById('difficultyChart'), {
        type: 'radar',
        data: {
            labels: difficultyData.map(d => d.difficulty),
            datasets: [{
                label: 'Average Score',
                data: difficultyData.map(d => d.average_score),
                borderColor: 'rgb(255, 99, 132)',
                backgroundColor: 'rgba(255, 99, 132, 0.2)'
            }, {
                label: 'Pass Rate',
                data: difficultyData.map(d => d.pass_rate),
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.2)'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                r: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            }
        }
    });
});
</script>
@endsection
