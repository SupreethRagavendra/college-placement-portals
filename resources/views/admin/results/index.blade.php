@extends('layouts.admin')

@section('title', 'Assessment Results')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Assessment Results</h1>
        <p class="text-muted mb-0">{{ $assessment->title }}</p>
    </div>
    <div>
        <a href="{{ route('admin.results.export', $assessment) }}" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Export to CSV
        </a>
        <a href="{{ route('admin.assessments.show', $assessment) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="card-title">Total Attempts</h6>
                <h2 class="mb-0">{{ $studentAssessments->total() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="card-title">Passed</h6>
                <h2 class="mb-0">{{ $studentAssessments->where('pass_status', 'pass')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6 class="card-title">Failed</h6>
                <h2 class="mb-0">{{ $studentAssessments->where('pass_status', 'fail')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="card-title">Average Score</h6>
                <h2 class="mb-0">{{ $studentAssessments->count() > 0 ? number_format($studentAssessments->avg('percentage'), 2) : 0 }}%</h2>
            </div>
        </div>
    </div>
</div>

<!-- Results Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Student Results</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Score</th>
                    <th>Percentage</th>
                    <th>Status</th>
                    <th>Time Taken</th>
                    <th>Submitted At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($studentAssessments as $result)
                <tr>
                    <td>
                        <strong>{{ $result->student->name ?? 'Unknown' }}</strong>
                    </td>
                    <td>{{ $result->student->email ?? 'N/A' }}</td>
                    <td>
                        <strong>{{ $result->obtained_marks }}/{{ $result->total_marks }}</strong>
                    </td>
                    <td>
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar bg-{{ $result->percentage >= 50 ? 'success' : 'danger' }}" 
                                 role="progressbar" 
                                 style="width: {{ $result->percentage }}%"
                                 aria-valuenow="{{ $result->percentage }}" 
                                 aria-valuemin="0" 
                                 aria-valuemax="100">
                                {{ number_format($result->percentage, 2) }}%
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($result->pass_status == 'pass')
                            <span class="badge bg-success">Pass</span>
                        @elseif($result->pass_status == 'fail')
                            <span class="badge bg-danger">Fail</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($result->status) }}</span>
                        @endif
                    </td>
                    <td>{{ $result->formatted_time_taken ?? 'N/A' }}</td>
                    <td>
                        @if($result->submit_time)
                            {{ $result->submit_time->format('M d, Y') }}<br>
                            <small class="text-muted">{{ $result->submit_time->format('H:i A') }}</small>
                        @else
                            <span class="text-muted">Not submitted</span>
                        @endif
                    </td>
                    <td>
                        @if($result->status == 'completed')
                        <a href="{{ route('admin.results.show', $result) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> View Details
                        </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted">No student has attempted this assessment yet</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($studentAssessments->hasPages())
    <div class="card-footer">
        {{ $studentAssessments->links() }}
    </div>
    @endif
</div>

<!-- Performance Chart -->
@if($studentAssessments->count() > 0)
<div class="card mt-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-chart-pie"></i> Performance Distribution</h5>
    </div>
    <div class="card-body">
        <canvas id="performanceChart" height="80"></canvas>
    </div>
</div>
@endif

@endsection

@section('scripts')
@if($studentAssessments->count() > 0)
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('performanceChart').getContext('2d');
    
    // Calculate score ranges
    const ranges = {
        '90-100%': {{ $studentAssessments->filter(fn($r) => $r->percentage >= 90)->count() }},
        '80-89%': {{ $studentAssessments->filter(fn($r) => $r->percentage >= 80 && $r->percentage < 90)->count() }},
        '70-79%': {{ $studentAssessments->filter(fn($r) => $r->percentage >= 70 && $r->percentage < 80)->count() }},
        '60-69%': {{ $studentAssessments->filter(fn($r) => $r->percentage >= 60 && $r->percentage < 70)->count() }},
        '50-59%': {{ $studentAssessments->filter(fn($r) => $r->percentage >= 50 && $r->percentage < 60)->count() }},
        'Below 50%': {{ $studentAssessments->filter(fn($r) => $r->percentage < 50)->count() }}
    };

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: Object.keys(ranges),
            datasets: [{
                label: 'Number of Students',
                data: Object.values(ranges),
                backgroundColor: [
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(153, 102, 255, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(255, 159, 64, 0.6)',
                    'rgba(255, 99, 132, 0.6)'
                ],
                borderColor: [
                    'rgba(75, 192, 192, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(255, 159, 64, 1)',
                    'rgba(255, 99, 132, 1)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                title: {
                    display: true,
                    text: 'Score Distribution'
                }
            }
        }
    });
});
</script>
@endif
@endsection

@section('styles')
<style>
.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.progress {
    font-size: 0.875rem;
    font-weight: bold;
}

.table td {
    vertical-align: middle;
}
</style>
@endsection