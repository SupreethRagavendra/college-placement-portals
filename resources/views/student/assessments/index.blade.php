@extends('layouts.student')

@section('title', 'Available Assessments')

@section('content')
<div class="mb-4">
    <h1><i class="fas fa-clipboard-list"></i> Available Assessments</h1>
    <p class="text-muted">Browse and take assessments to test your skills</p>
</div>

@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('info'))
    <div class="alert alert-info alert-dismissible fade show">
        {{ session('info') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('student.assessments.index') }}">
            <div class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search assessments..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-select">
                        <option value="">All Categories</option>
                        <option value="Aptitude" {{ request('category') == 'Aptitude' ? 'selected' : '' }}>Aptitude</option>
                        <option value="Technical" {{ request('category') == 'Technical' ? 'selected' : '' }}>Technical</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="difficulty" class="form-select">
                        <option value="">All Difficulty Levels</option>
                        <option value="easy" {{ request('difficulty') == 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ request('difficulty') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ request('difficulty') == 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Assessments Grid -->
<div class="row">
    @forelse($assessments as $assessment)
    <div class="col-md-6 mb-4">
        <div class="card h-100 assessment-card">
            <div class="card-header bg-gradient-{{ $assessment->category == 'Aptitude' ? 'primary' : 'success' }} text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">{{ $assessment->title }}</h5>
                    <span class="badge bg-light text-dark">{{ $assessment->category }}</span>
                </div>
            </div>
            <div class="card-body">
                @if($assessment->description)
                <p class="text-muted">{{ Str::limit($assessment->description, 120) }}</p>
                @endif

                <div class="row g-3 mt-2">
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-clock text-primary me-2"></i>
                            <div>
                                <small class="text-muted d-block">Duration</small>
                                <strong>{{ $assessment->duration }} min</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-question-circle text-info me-2"></i>
                            <div>
                                <small class="text-muted d-block">Questions</small>
                                <strong>{{ $assessment->questions->count() }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-star text-warning me-2"></i>
                            <div>
                                <small class="text-muted d-block">Total Marks</small>
                                <strong>{{ $assessment->total_marks }}</strong>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-chart-line text-success me-2"></i>
                            <div>
                                <small class="text-muted d-block">Pass Mark</small>
                                <strong>{{ $assessment->pass_percentage }}%</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <span class="badge bg-{{ $assessment->difficulty_level == 'easy' ? 'success' : ($assessment->difficulty_level == 'hard' ? 'danger' : 'warning') }}">
                        <i class="fas fa-signal"></i> {{ ucfirst($assessment->difficulty_level) }}
                    </span>
                    @if($assessment->allow_multiple_attempts)
                        <span class="badge bg-info">
                            <i class="fas fa-redo"></i> Multiple Attempts
                        </span>
                    @endif
                    @if($assessment->show_results_immediately)
                        <span class="badge bg-secondary">
                            <i class="fas fa-eye"></i> Instant Results
                        </span>
                    @endif
                </div>

                @if($assessment->student_attempt)
                <div class="alert alert-info mt-3 mb-0">
                    <i class="fas fa-info-circle"></i> 
                    @if($assessment->student_attempt->status == 'completed')
                        You scored <strong>{{ number_format($assessment->student_attempt->percentage, 2) }}%</strong>
                        @if(!$assessment->allow_multiple_attempts)
                            (Completed)
                        @endif
                    @else
                        In Progress
                    @endif
                </div>
                @endif
            </div>
            <div class="card-footer bg-transparent">
                @if($assessment->student_attempt && $assessment->student_attempt->status == 'completed')
                    {{-- Student has completed the assessment --}}
                    @if($assessment->allow_multiple_attempts)
                        {{-- Multiple attempts allowed - show both buttons --}}
                        <div class="d-flex gap-2">
                            <a href="{{ route('student.assessments.result', $assessment) }}" class="btn btn-outline-primary flex-fill">
                                <i class="fas fa-eye"></i> View Results
                            </a>
                            <a href="{{ route('student.assessments.show', $assessment) }}" class="btn btn-success flex-fill">
                                <i class="fas fa-redo"></i> Retake
                            </a>
                        </div>
                    @else
                        {{-- Multiple attempts NOT allowed - only show view results --}}
                        <a href="{{ route('student.assessments.result', $assessment) }}" class="btn btn-primary w-100">
                            <i class="fas fa-eye"></i> View Results
                        </a>
                    @endif
                @elseif($assessment->student_attempt && $assessment->student_attempt->status == 'started')
                    {{-- Student has started but not completed --}}
                    <a href="{{ route('student.assessments.take', [$assessment, 'attempt' => $assessment->student_attempt->id]) }}" class="btn btn-warning w-100">
                        <i class="fas fa-play"></i> Continue Assessment
                    </a>
                @else
                    {{-- Student has not attempted yet --}}
                    <a href="{{ route('student.assessments.show', $assessment) }}" class="btn btn-primary w-100">
                        <i class="fas fa-play-circle"></i> Start Assessment
                    </a>
                @endif
            </div>
        </div>
    </div>
    @empty
    <div class="col-12">
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No Assessments Available</h4>
                <p class="text-muted">There are no active assessments at the moment. Please check back later.</p>
            </div>
        </div>
    </div>
    @endforelse
</div>

@if($assessments->hasPages())
<div class="mt-4">
    {{ $assessments->links() }}
</div>
@endif

@endsection

@section('styles')
<style>
.assessment-card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.assessment-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.15);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.bg-gradient-success {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.card-header h5 {
    font-size: 1.1rem;
    font-weight: 600;
}

.badge {
    padding: 0.4rem 0.6rem;
    font-size: 0.75rem;
}

small {
    font-size: 0.7rem;
}

strong {
    font-size: 0.95rem;
}

.d-flex.align-items-center i {
    font-size: 1.2rem;
}
</style>
@endsection