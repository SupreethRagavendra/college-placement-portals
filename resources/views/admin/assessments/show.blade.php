@extends('layouts.admin')

@section('title', 'Assessment Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Assessment Details</h1>
    <div>
        <a href="{{ route('admin.assessments.edit', $assessment) }}" class="btn btn-warning">
            <i class="fas fa-edit"></i> Edit
        </a>
        <a href="{{ route('admin.assessments.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

<!-- Assessment Info -->
<div class="row mb-4">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-info-circle"></i> Assessment Information</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <strong>Title:</strong><br>
                        <span class="text-muted">{{ $assessment->title }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Category:</strong><br>
                        <span class="badge bg-info">{{ $assessment->category }}</span>
                    </div>
                    <div class="col-md-12 mb-3">
                        <strong>Description:</strong><br>
                        <span class="text-muted">{{ $assessment->description ?: 'No description provided' }}</span>
                    </div>
                    <div class="col-md-4 mb-3">
                        <strong>Duration:</strong><br>
                        <span class="text-muted">{{ $assessment->duration }} minutes</span>
                    </div>
                    <div class="col-md-4 mb-3">
                        <strong>Total Marks:</strong><br>
                        <span class="text-muted">{{ $assessment->total_marks }}</span>
                    </div>
                    <div class="col-md-4 mb-3">
                        <strong>Pass Percentage:</strong><br>
                        <span class="text-muted">{{ $assessment->pass_percentage }}%</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Difficulty Level:</strong><br>
                        <span class="badge bg-{{ $assessment->difficulty_level == 'easy' ? 'success' : ($assessment->difficulty_level == 'hard' ? 'danger' : 'warning') }}">
                            {{ ucfirst($assessment->difficulty_level) }}
                        </span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Status:</strong><br>
                        @if($assessment->status == 'active')
                            <span class="badge bg-success">Active</span>
                        @elseif($assessment->status == 'inactive')
                            <span class="badge bg-secondary">Inactive</span>
                        @else
                            <span class="badge bg-warning">Draft</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>Start Date:</strong><br>
                        <span class="text-muted">{{ $assessment->start_date ? $assessment->start_date->format('M d, Y H:i') : 'Not set' }}</span>
                    </div>
                    <div class="col-md-6 mb-3">
                        <strong>End Date:</strong><br>
                        <span class="text-muted">{{ $assessment->end_date ? $assessment->end_date->format('M d, Y H:i') : 'Not set' }}</span>
                    </div>
                    <div class="col-md-12">
                        <strong>Options:</strong><br>
                        @if($assessment->allow_multiple_attempts)
                            <span class="badge bg-info">Multiple Attempts Allowed</span>
                        @endif
                        @if($assessment->show_results_immediately)
                            <span class="badge bg-info">Show Results Immediately</span>
                        @endif
                        @if($assessment->show_correct_answers)
                            <span class="badge bg-info">Show Correct Answers</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Statistics</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <strong>Total Questions:</strong><br>
                    <h3 class="text-primary">{{ $assessment->questions_count ?? 0 }}</h3>
                </div>
                <div class="mb-3">
                    <strong>Total Attempts:</strong><br>
                    <h3 class="text-info">{{ $assessment->student_assessments_count ?? 0 }}</h3>
                </div>
                @if(($assessment->student_assessments_count ?? 0) > 0)
                <div class="mb-3">
                    <strong>Average Score:</strong><br>
                    <h3 class="text-success">{{ number_format($assessment->average_score ?? 0, 2) }}%</h3>
                </div>
                <div class="mb-3">
                    <strong>Pass Rate:</strong><br>
                    <h3 class="text-warning">{{ number_format($assessment->pass_rate ?? 0, 2) }}%</h3>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="card mb-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="fas fa-tasks"></i> Quick Actions</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-2">
                <a href="{{ route('admin.assessments.questions', $assessment) }}" class="btn btn-info btn-lg w-100">
                    <i class="fas fa-question-circle"></i><br>
                    Manage Questions
                </a>
            </div>
            <div class="col-md-3 mb-2">
                <a href="{{ route('admin.assessments.add-question', $assessment) }}" class="btn btn-primary btn-lg w-100">
                    <i class="fas fa-plus-circle"></i><br>
                    Add Question
                </a>
            </div>
            <div class="col-md-3 mb-2">
                <a href="{{ route('admin.results.index', $assessment) }}" class="btn btn-warning btn-lg w-100">
                    <i class="fas fa-chart-line"></i><br>
                    View Results
                </a>
            </div>
            <div class="col-md-3 mb-2">
                <form action="{{ route('admin.assessments.duplicate', $assessment) }}" method="POST" class="d-inline w-100">
                    @csrf
                    <button type="submit" class="btn btn-secondary btn-lg w-100">
                        <i class="fas fa-copy"></i><br>
                        Duplicate
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Recent Attempts -->
@if($assessment->studentAssessments && $assessment->studentAssessments->count() > 0)
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-history"></i> Recent Attempts (Last 10)</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Student</th>
                    <th>Score</th>
                    <th>Percentage</th>
                    <th>Status</th>
                    <th>Time Taken</th>
                    <th>Submitted At</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($assessment->studentAssessments as $attempt)
                <tr>
                    <td>{{ $attempt->student->name ?? 'N/A' }}</td>
                    <td>{{ $attempt->obtained_marks }}/{{ $attempt->total_marks }}</td>
                    <td>{{ number_format($attempt->percentage, 2) }}%</td>
                    <td>
                        @if($attempt->pass_status == 'pass')
                            <span class="badge bg-success">Pass</span>
                        @elseif($attempt->pass_status == 'fail')
                            <span class="badge bg-danger">Fail</span>
                        @else
                            <span class="badge bg-secondary">{{ ucfirst($attempt->status) }}</span>
                        @endif
                    </td>
                    <td>{{ $attempt->formatted_time_taken ?? 'N/A' }}</td>
                    <td>{{ $attempt->submit_time ? $attempt->submit_time->format('M d, Y H:i') : 'Not submitted' }}</td>
                    <td>
                        @if($attempt->status == 'completed')
                        <a href="{{ route('admin.results.show', $attempt) }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i> View
                        </a>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

@endsection

@section('styles')
<style>
.btn-lg {
    padding: 1rem;
    font-size: 0.9rem;
}

.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.card-header {
    font-weight: 600;
}
</style>
@endsection