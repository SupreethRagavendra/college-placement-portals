@extends('layouts.student')

@section('title', 'Assessment Result')

@section('styles')
<style>
    .result-hero {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        padding: 40px;
        border-radius: 16px;
        margin-bottom: 30px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    .result-hero::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
    }
    .score-circle {
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        border: 4px solid rgba(255,255,255,0.3);
    }
    .score-circle .score-value {
        font-size: 3rem;
        font-weight: bold;
        line-height: 1;
    }
    .score-circle .score-label {
        font-size: 0.875rem;
        opacity: 0.9;
        margin-top: 5px;
    }
    .stat-box {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        margin-bottom: 10px;
    }
    .bg-light-success {
        background-color: rgba(25, 135, 84, 0.1) !important;
    }
    .bg-light-danger {
        background-color: rgba(220, 53, 69, 0.1) !important;
    }
    .question-review {
        transition: all 0.3s ease;
        border-radius: 12px;
    }
    .question-review:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.1);
    }
    .review-card {
        border: none;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        border-radius: 12px;
    }
</style>
@endsection

@section('content')
<div class="result-hero">
    <div class="position-relative">
        @php
            $percentage = $result->total_questions > 0 ? round(($result->score / $result->total_questions) * 100, 1) : 0;
            $performanceText = $percentage >= 80 ? 'Excellent!' : ($percentage >= 60 ? 'Good Job!' : ($percentage >= 40 ? 'Keep Practicing!' : 'Need Improvement'));
            $performanceIcon = $percentage >= 80 ? 'fa-trophy' : ($percentage >= 60 ? 'fa-thumbs-up' : ($percentage >= 40 ? 'fa-star' : 'fa-book'));
        @endphp
        <div class="mb-3">
            <i class="fas {{ $performanceIcon }} fa-3x mb-3"></i>
            <h2 class="mb-2">{{ $performanceText }}</h2>
            <h4 class="opacity-90">{{ $assessment->name ?? 'Assessment' }}</h4>
        </div>
        <div class="score-circle">
            <div class="score-value">{{ $percentage }}%</div>
            <div class="score-label">Your Score</div>
        </div>
        <p class="mb-0 opacity-90">You answered {{ $result->score ?? 0 }} out of {{ $result->total_questions ?? 0 }} questions correctly</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="stat-box text-center">
            <div class="stat-icon bg-success-subtle text-success mx-auto">
                <i class="fas fa-check"></i>
            </div>
            <h3 class="mb-1">{{ $result->score ?? 0 }}</h3>
            <p class="text-muted mb-0">Correct Answers</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-box text-center">
            <div class="stat-icon bg-danger-subtle text-danger mx-auto">
                <i class="fas fa-times"></i>
            </div>
            <h3 class="mb-1">{{ ($result->total_questions ?? 0) - ($result->score ?? 0) }}</h3>
            <p class="text-muted mb-0">Incorrect Answers</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-box text-center">
            <div class="stat-icon bg-info-subtle text-info mx-auto">
                <i class="fas fa-clock"></i>
            </div>
            <h3 class="mb-1">{{ $result->submitted_at ? $result->submitted_at->format('M d, Y') : 'N/A' }}</h3>
            <p class="text-muted mb-0">Completed On</p>
        </div>
    </div>
</div>

<div class="d-flex gap-2 justify-content-center mb-4">
    <a href="{{ route('student.assessments.index') }}" class="btn btn-primary">
        <i class="fas fa-clipboard-list me-2"></i>Back to Assessments
    </a>
    <a href="{{ route('student.assessment.history') }}" class="btn btn-outline-secondary">
        <i class="fas fa-history me-2"></i>View History
    </a>
    @if($assessment->is_active)
    <a href="{{ route('student.assessments.show', $assessment) }}" class="btn btn-outline-success">
        <i class="fas fa-redo me-2"></i>Retake Assessment
    </a>
    @endif
</div>

<!-- Detailed Review Section -->
@if(!empty($detailedResults))
<div class="card review-card">
    <div class="card-header bg-white border-bottom">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h5 class="mb-1"><i class="fas fa-clipboard-check me-2 text-primary"></i>Detailed Answer Review</h5>
                <small class="text-muted">Review your answers and learn from the correct solutions</small>
            </div>
            <span class="badge bg-primary">{{ count($detailedResults) }} Questions</span>
        </div>
    </div>
    <div class="card-body p-4">
        @foreach($detailedResults as $index => $detail)
        <div class="question-review mb-4 p-4 border {{ $detail['is_correct'] ? 'border-success bg-light-success' : 'border-danger bg-light-danger' }}">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <span class="badge {{ $detail['is_correct'] ? 'bg-success' : 'bg-danger' }} mb-2">
                        <i class="fas {{ $detail['is_correct'] ? 'fa-check' : 'fa-times' }} me-1"></i>
                        {{ $detail['is_correct'] ? 'Correct' : 'Incorrect' }}
                    </span>
                    <h6 class="mb-0 text-muted">Question {{ $index + 1 }} of {{ count($detailedResults) }}</h6>
                </div>
            </div>
            
            <div class="question-text mb-4">
                <h6 class="fw-semibold">{{ $detail['question']->question }}</h6>
            </div>
            
            <div class="options">
                @php 
                    $options = is_array($detail['question']->options) ? $detail['question']->options : json_decode($detail['question']->options, true) ?? [];
                    $letters = ['A', 'B', 'C', 'D'];
                    $userAnswerLetter = strtoupper($detail['user_answer'] ?? '');
                    $correctAnswerLetter = strtoupper($detail['correct_answer'] ?? '');
                @endphp
                
                @foreach($options as $optIndex => $option)
                @php 
                    $currentLetter = $letters[$optIndex] ?? chr(65 + $optIndex);
                    $isCorrect = $currentLetter === $correctAnswerLetter;
                    $isUserAnswer = $currentLetter === $userAnswerLetter;
                @endphp
                <div class="form-check mb-3 p-3 rounded {{ $isCorrect ? 'bg-success-subtle' : ($isUserAnswer && !$detail['is_correct'] ? 'bg-danger-subtle' : 'bg-light') }}">
                    <input class="form-check-input" type="radio" disabled {{ $isUserAnswer ? 'checked' : '' }}>
                    <label class="form-check-label d-flex align-items-center w-100">
                        <span class="badge bg-secondary me-3">{{ $currentLetter }}</span>
                        <span class="flex-grow-1 {{ $isCorrect ? 'fw-bold text-success' : ($isUserAnswer && !$detail['is_correct'] ? 'text-danger' : '') }}">
                            {{ $option }}
                        </span>
                        @if($isCorrect)
                            <i class="fas fa-check-circle text-success fa-lg ms-2"></i>
                        @elseif($isUserAnswer && !$detail['is_correct'])
                            <i class="fas fa-times-circle text-danger fa-lg ms-2"></i>
                        @endif
                    </label>
                </div>
                @endforeach
            </div>
            
            @if(!$detail['is_correct'])
            <div class="alert alert-info mt-3 mb-0 d-flex align-items-start">
                <i class="fas fa-lightbulb fa-lg me-3 mt-1"></i>
                <div>
                    @php
                        $correctAnswerIndex = array_search($correctAnswerLetter, $letters);
                        $correctOptionText = ($correctAnswerIndex !== false && isset($options[$correctAnswerIndex])) ? $options[$correctAnswerIndex] : 'N/A';
                    @endphp
                    <strong>Correct Answer:</strong> Option {{ $correctAnswerLetter }} - {{ $correctOptionText }}
                </div>
            </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif
@endsection
