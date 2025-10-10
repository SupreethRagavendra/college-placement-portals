@extends('layouts.student')

@section('title', 'Assessment History')

@section('styles')
<style>
    .history-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: #fff;
        padding: 24px;
        border-radius: 12px;
        margin-bottom: 24px;
    }
    .stat-box {
        background: rgba(255,255,255,0.2);
        padding: 12px 20px;
        border-radius: 8px;
        display: inline-block;
        margin-right: 16px;
    }
    .stat-box .stat-value {
        font-size: 1.5rem;
        font-weight: bold;
    }
    .stat-box .stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }
    .history-card {
        border: none;
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        border-radius: 12px;
    }
    .score-badge {
        font-size: 0.875rem;
        font-weight: 600;
        padding: 4px 8px;
        border-radius: 6px;
    }
    .score-excellent { background: #d1fae5; color: #065f46; }
    .score-good { background: #dbeafe; color: #1e40af; }
    .score-average { background: #fed7aa; color: #9a3412; }
    .score-poor { background: #fee2e2; color: #991b1b; }
    .time-badge {
        color: #6b7280;
        font-size: 0.875rem;
    }
</style>
@endsection

@section('content')
<div class="history-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-2"><i class="fas fa-history me-2"></i>Assessment History</h1>
            <p class="mb-0 opacity-90">View all your past assessment attempts and results</p>
        </div>
        <div>
            @php
                $totalAttempts = count($results ?? []);
                $avgScore = $totalAttempts > 0 ? collect($results)->avg(function($r) {
                    return ($r['total_questions'] ?? 0) > 0 ? ($r['score'] ?? 0) / $r['total_questions'] * 100 : 0;
                }) : 0;
            @endphp
            <div class="stat-box">
                <div class="stat-value">{{ $totalAttempts }}</div>
                <div class="stat-label">Total Attempts</div>
            </div>
            <div class="stat-box">
                <div class="stat-value">{{ round($avgScore) }}%</div>
                <div class="stat-label">Average Score</div>
            </div>
        </div>
    </div>
</div>

<div class="card history-card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr class="border-bottom">
                        <th class="px-4 py-3 text-muted fw-semibold">
                            <i class="fas fa-clipboard-list me-2"></i>Assessment
                        </th>
                        <th class="px-4 py-3 text-muted fw-semibold">
                            <i class="fas fa-tag me-2"></i>Category
                        </th>
                        <th class="px-4 py-3 text-muted fw-semibold">
                            <i class="fas fa-chart-simple me-2"></i>Score
                        </th>
                        <th class="px-4 py-3 text-muted fw-semibold">
                            <i class="fas fa-clock me-2"></i>Time Taken
                        </th>
                        <th class="px-4 py-3 text-muted fw-semibold">
                            <i class="fas fa-calendar me-2"></i>Date
                        </th>
                        <th class="px-4 py-3 text-muted fw-semibold text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($results as $result)
                    @php 
                        $assessment = $assessmentsById[(int)($result['assessment_id'] ?? 0)] ?? null;
                        $scorePercent = ($result['total_questions'] ?? 0) > 0 ? 
                            round(($result['score'] ?? 0) / $result['total_questions'] * 100) : 0;
                        $scoreClass = $scorePercent >= 80 ? 'score-excellent' : 
                                     ($scorePercent >= 60 ? 'score-good' : 
                                     ($scorePercent >= 40 ? 'score-average' : 'score-poor'));
                    @endphp
                    <tr>
                        <td class="px-4 py-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3">
                                    <i class="fas fa-file-alt text-primary fa-lg"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold">{{ $assessment['name'] ?? 'Unknown Assessment' }}</div>
                                    <small class="text-muted">{{ $assessment['questions_count'] ?? 0 }} questions</small>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge bg-{{ ($assessment['category'] ?? '')==='Aptitude' ? 'primary' : 'success' }}">
                                {{ $assessment['category'] ?? 'General' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div>
                                <span class="score-badge {{ $scoreClass }}">
                                    {{ $result['score'] ?? 0 }}/{{ $result['total_questions'] ?? 0 }}
                                </span>
                                <div class="small text-muted mt-1">{{ $scorePercent }}%</div>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="time-badge">
                                <i class="far fa-clock me-1"></i>
                                @php
                                    $seconds = $result['time_taken'] ?? 0;
                                    $minutes = floor($seconds / 60);
                                    $remainingSeconds = $seconds % 60;
                                @endphp
                                {{ $minutes }}m {{ $remainingSeconds }}s
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div>
                                <div class="fw-semibold">{{ \Carbon\Carbon::parse($result['submitted_at'] ?? now())->format('M d, Y') }}</div>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($result['submitted_at'] ?? now())->format('h:i A') }}</small>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php $assessmentModel = \App\Models\Assessment::find($result['assessment_id'] ?? 0); @endphp
                            @if($assessmentModel && $assessmentModel->is_active)
                                <div class="btn-group" role="group">
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('student.assessments.result', $assessmentModel) }}">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                    <a class="btn btn-sm btn-outline-success" href="{{ route('student.assessments.show', $assessmentModel) }}">
                                        <i class="fas fa-redo me-1"></i>Retake
                                    </a>
                                </div>
                            @elseif($assessmentModel)
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('student.assessments.result', $assessmentModel) }}">
                                    <i class="fas fa-eye me-1"></i>View Result
                                </a>
                            @else
                                <span class="text-muted small">Not available</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <div>
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Assessment History</h5>
                                <p class="text-muted mb-3">You haven't taken any assessments yet.</p>
                                <a href="{{ route('student.assessments.index') }}" class="btn btn-primary">
                                    <i class="fas fa-clipboard-list me-2"></i>Browse Assessments
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if(count($results ?? []) > 10)
<div class="mt-3 text-center">
    <small class="text-muted">Showing {{ count($results) }} assessment attempts</small>
</div>
@endif
@endsection
