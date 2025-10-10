@extends('layouts.student')

@section('title', 'Assessment Details')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Assessment Header -->
            <div class="card mb-4 border-0 shadow-lg">
                <div class="card-header bg-gradient-primary text-white py-4">
                    <h2 class="mb-0"><i class="fas fa-clipboard-check"></i> {{ $assessment->title }}</h2>
                </div>
                <div class="card-body p-4">
                    @if($assessment->description)
                    <div class="alert alert-info mb-4">
                        <i class="fas fa-info-circle"></i> {{ $assessment->description }}
                    </div>
                    @endif

                    <h5 class="mb-3"><i class="fas fa-list-check"></i> Assessment Details</h5>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="info-box">
                                <i class="fas fa-tag text-primary"></i>
                                <div>
                                    <small class="text-muted">Category</small>
                                    <p class="mb-0"><strong>{{ $assessment->category }}</strong></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <i class="fas fa-clock text-warning"></i>
                                <div>
                                    <small class="text-muted">Duration</small>
                                    <p class="mb-0"><strong>{{ $assessment->duration }} minutes</strong></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <i class="fas fa-question-circle text-info"></i>
                                <div>
                                    <small class="text-muted">Total Questions</small>
                                    <p class="mb-0"><strong>{{ $assessment->questions->count() }}</strong></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <i class="fas fa-star text-success"></i>
                                <div>
                                    <small class="text-muted">Total Marks</small>
                                    <p class="mb-0"><strong>{{ $assessment->total_marks }}</strong></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <i class="fas fa-chart-line text-danger"></i>
                                <div>
                                    <small class="text-muted">Pass Percentage</small>
                                    <p class="mb-0"><strong>{{ $assessment->pass_percentage }}%</strong></p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box">
                                <i class="fas fa-signal text-warning"></i>
                                <div>
                                    <small class="text-muted">Difficulty Level</small>
                                    <p class="mb-0"><strong>{{ ucfirst($assessment->difficulty_level) }}</strong></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="mb-3"><i class="fas fa-exclamation-triangle"></i> Important Instructions</h5>
                    <ul class="list-group mb-4">
                        <li class="list-group-item">
                            <i class="fas fa-check-circle text-success"></i> 
                            Read each question carefully before answering
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-clock text-warning"></i> 
                            You have <strong>{{ $assessment->duration }} minutes</strong> to complete the assessment
                        </li>
                        <li class="list-group-item">
                            <i class="fas fa-save text-info"></i> 
                            Your progress will be auto-saved periodically
                        </li>
                        @if($assessment->allow_multiple_attempts)
                        <li class="list-group-item">
                            <i class="fas fa-redo text-primary"></i> 
                            Multiple attempts are allowed for this assessment
                        </li>
                        @else
                        <li class="list-group-item">
                            <i class="fas fa-ban text-danger"></i> 
                            Only <strong>one attempt</strong> is allowed for this assessment
                        </li>
                        @endif
                        @if($assessment->show_results_immediately)
                        <li class="list-group-item">
                            <i class="fas fa-eye text-success"></i> 
                            Results will be shown immediately after submission
                        </li>
                        @endif
                        @if($assessment->show_correct_answers)
                        <li class="list-group-item">
                            <i class="fas fa-lightbulb text-warning"></i> 
                            Correct answers will be shown in the results
                        </li>
                        @endif
                        <li class="list-group-item">
                            <i class="fas fa-exclamation-triangle text-danger"></i> 
                            Make sure you have a stable internet connection
                        </li>
                    </ul>

                    <!-- Start Button -->
                    <form action="{{ route('student.assessments.start', $assessment) }}" method="POST">
                        @csrf
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-lg btn-success" onclick="return confirm('Are you ready to start the assessment? The timer will begin immediately.')">
                                <i class="fas fa-play-circle"></i> Start Assessment Now
                            </button>
                            <a href="{{ route('student.assessments.index') }}" class="btn btn-lg btn-outline-secondary">
                                <i class="fas fa-arrow-left"></i> Back to Assessments
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.info-box {
    display: flex;
    align-items: center;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    border-left: 4px solid #667eea;
}

.info-box i {
    font-size: 2rem;
    margin-right: 1rem;
}

.info-box small {
    font-size: 0.75rem;
    display: block;
    margin-bottom: 0.25rem;
}

.info-box p {
    font-size: 1rem;
}

.card {
    border-radius: 1rem;
}

.list-group-item {
    border-left: 3px solid transparent;
    padding-left: 1rem;
}

.list-group-item:hover {
    background-color: #f8f9fa;
    border-left-color: #667eea;
}

.btn-lg {
    padding: 1rem 2rem;
    font-size: 1.1rem;
    font-weight: 600;
}

.shadow-lg {
    box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
}
</style>
@endsection