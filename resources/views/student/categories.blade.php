@extends('layouts.student')

@section('title', 'Categories')

@section('styles')
<style>
    .hero-section { 
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
        color: #fff; 
        padding: 36px 24px; 
        border-radius: 16px;
        margin-bottom: 30px;
        position: relative; 
        overflow: hidden; 
    }
    .hero-section .hero-icon { 
        position: absolute; 
        right: -40px; 
        bottom: -20px; 
        font-size: 8rem; 
        opacity: 0.15; 
    }
    .category-card { 
        border: 0; 
        box-shadow: 0 8px 20px rgba(0,0,0,0.06); 
        border-radius: 14px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        height: 100%;
    }
    .category-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.12);
    }
    .category-stats {
        font-size: 0.875rem;
        color: #6c757d;
        margin-top: 0.5rem;
    }
    .category-icon {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    .aptitude-icon {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }
    .technical-icon {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }
    .info-card {
        border: none;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border-radius: 12px;
    }
</style>
@endsection

@section('content')
<div class="hero-section">
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="display-6 fw-bold mb-2"><i class="fas fa-layer-group me-3"></i>Assessment Categories</h1>
            <p class="mb-0 opacity-90">Select a test category to begin your assessment journey and evaluate your skills.</p>
        </div>
        <i class="fa-solid fa-layer-group hero-icon"></i>
    </div>
</div>
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-3">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row justify-content-center g-4 mb-4">
    @forelse ($categories as $cat)
        <div class="col-12 col-md-6">
            <div class="card category-card">
                <div class="card-body text-center p-5">
                    <div class="category-icon {{ $cat->name === 'Aptitude' ? 'aptitude-icon' : 'technical-icon' }}">
                        <i class="fa-solid {{ $cat->name === 'Aptitude' ? 'fa-brain' : 'fa-microchip' }} fa-2x text-white"></i>
                    </div>
                    <h4 class="card-title mb-3 fw-bold">{{ $cat->name }}</h4>
                    <p class="text-muted mb-4">
                        {{ $cat->name === 'Aptitude' ? 
                           'Test your quantitative aptitude, logical reasoning, and verbal ability skills.' : 
                           'Evaluate your programming, databases, data structures, and CS fundamentals.' }}
                    </p>
                    
                    <div class="d-flex justify-content-center gap-3 mb-4">
                        <div class="text-center">
                            <div class="fw-bold h4 mb-1">{{ $cat->total_assessments }}</div>
                            <small class="text-muted">{{ Str::plural('Assessment', $cat->total_assessments) }}</small>
                        </div>
                        <div class="vr"></div>
                        <div class="text-center">
                            <div class="fw-bold h4 mb-1">{{ $cat->total_questions }}</div>
                            <small class="text-muted">{{ Str::plural('Question', $cat->total_questions) }}</small>
                        </div>
                    </div>

                    @if($cat->total_assessments > 0)
                        <a href="{{ route('student.test', $cat->id) }}" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-play-circle me-2"></i>Start Assessment
                        </a>
                    @else
                        <button class="btn btn-secondary btn-lg w-100" disabled>
                            <i class="fas fa-lock me-2"></i>No Assessments Available
                        </button>
                    @endif
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card category-card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
                    <h4 class="text-muted">No Categories Available</h4>
                    <p class="text-muted">There are no assessment categories available at the moment. Please check back later.</p>
                    <a href="{{ route('student.dashboard') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-home me-2"></i>Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    @endforelse
</div>

<div class="row">
    <div class="col-12">
        <div class="card info-card">
            <div class="card-body p-4">
                <h5 class="card-title mb-4 fw-bold">
                    <i class="fas fa-info-circle text-primary me-2"></i>Assessment Guidelines
                </h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex">
                            <div class="me-3">
                                <div class="rounded-circle bg-success-subtle p-2 text-success">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">Multiple Assessments</h6>
                                <p class="text-muted small mb-0">Each category contains various assessments to test different skills</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex">
                            <div class="me-3">
                                <div class="rounded-circle bg-warning-subtle p-2 text-warning">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">Time Limits</h6>
                                <p class="text-muted small mb-0">Each assessment has specific time limits to complete</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex">
                            <div class="me-3">
                                <div class="rounded-circle bg-info-subtle p-2 text-info">
                                    <i class="fas fa-chart-bar"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">Instant Results</h6>
                                <p class="text-muted small mb-0">Get detailed results and analysis immediately after submission</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="d-flex">
                            <div class="me-3">
                                <div class="rounded-circle bg-secondary-subtle p-2 text-secondary">
                                    <i class="fas fa-history"></i>
                                </div>
                            </div>
                            <div>
                                <h6 class="mb-1">Track Progress</h6>
                                <p class="text-muted small mb-0">Review your assessment history and performance trends anytime</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
