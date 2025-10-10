@extends('layouts.student')

@section('title', 'My Results')

@section('content')
<div class="mb-4">
    <h1><i class="fas fa-chart-bar"></i> My Assessment Results</h1>
    <p class="text-muted">View your performance across all assessments</p>
</div>

@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Overall Statistics -->
@if($studentAssessments->count() > 0)
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Total Assessments</h6>
                <h2 class="mb-0">{{ $studentAssessments->total() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Passed</h6>
                <h2 class="mb-0">{{ $studentAssessments->where('pass_status', 'pass')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Failed</h6>
                <h2 class="mb-0">{{ $studentAssessments->where('pass_status', 'fail')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white">
            <div class="card-body">
                <h6 class="text-uppercase">Average Score</h6>
                <h2 class="mb-0">{{ number_format($studentAssessments->avg('percentage'), 2) }}%</h2>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Results Table -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list"></i> Assessment History</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th>Assessment</th>
                    <th>Category</th>
                    <th>Score</th>
                    <th>Percentage</th>
                    <th>Status</th>
                    <th>Time Taken</th>
                    <th>Submitted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($studentAssessments as $result)
                <tr>
                    <td>
                        <strong>{{ $result->assessment->title }}</strong>
                    </td>
                    <td>
                        <span class="badge bg-info">{{ $result->assessment->category }}</span>
                    </td>
                    <td>
                        <strong>{{ $result->obtained_marks }}/{{ $result->total_marks }}</strong>
                    </td>
                    <td>
                        <div class="progress" style="height: 25px; width: 100px;">
                            <div class="progress-bar bg-{{ $result->percentage >= 50 ? 'success' : 'danger' }}" 
                                 role="progressbar" 
                                 style="width: {{ $result->percentage }}%">
                                {{ number_format($result->percentage, 1) }}%
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($result->pass_status == 'pass')
                            <span class="badge bg-success"><i class="fas fa-check-circle"></i> Pass</span>
                        @elseif($result->pass_status == 'fail')
                            <span class="badge bg-danger"><i class="fas fa-times-circle"></i> Fail</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($result->status) }}</span>
                        @endif
                    </td>
                    <td>{{ $result->formatted_time_taken }}</td>
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
                            <a href="{{ route('student.results.show', $result) }}" class="btn btn-sm btn-primary">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-5">
                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                        <h5 class="text-muted">No Results Yet</h5>
                        <p class="text-muted">You haven't completed any assessments yet.</p>
                        <a href="{{ route('student.assessments.index') }}" class="btn btn-primary">
                            <i class="fas fa-play-circle"></i> Browse Assessments
                        </a>
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

@endsection

@section('styles')
<style>
.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border: none;
}

.progress {
    font-size: 0.85rem;
    font-weight: bold;
}

.table td {
    vertical-align: middle;
}
</style>
@endsection