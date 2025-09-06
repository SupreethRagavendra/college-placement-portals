<x-app-layout>
    @php
        $totalTests = $totalTests ?? 0;
        $avgScore = isset($avgScore) ? round($avgScore) : 0;
        $rankText = $rankText ?? '—';
        $trendLabels = $trendLabels ?? [];
        $trendScores = $trendScores ?? [];
        $recentTests = $recentTests ?? [];
    @endphp

    <style>
        .hero-dashboard {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 36px 0;
            position: relative;
            overflow: hidden;
        }
        .hero-dashboard .hero-icon {
            position: absolute;
            right: -40px;
            bottom: -20px;
            font-size: 10rem;
            opacity: 0.12;
        }
        .stat-card {
            border: 0;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
            border-radius: 14px;
        }
        .icon-badge {
            width: 42px; height: 42px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
        }
    </style>

    <section class="hero-dashboard">
        <div class="container">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                <div>
                    <h1 class="display-6 fw-bold mb-1">Welcome back, {{ Auth::user()->name ?? 'Student' }}</h1>
                    <p class="mb-0 opacity-75">Track your progress, take tests, and analyze performance.</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('student.assessments') }}" class="btn btn-light btn-lg">
                        <i class="fa-solid fa-clipboard-list me-2"></i>View Assessments
                    </a>
                    <a href="{{ route('student.assessment.history') }}" class="btn btn-outline-light btn-lg">
                        <i class="fa-solid fa-history me-2"></i>Test History
                    </a>
                </div>
            </div>
            <i class="fa-solid fa-graduation-cap hero-icon"></i>
        </div>
    </section>

    <div class="container py-4">

        <div class="row g-4 mb-4">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase small text-muted">Tests Taken</div>
                            <div class="fs-3 fw-bold">{{ $totalTests }}</div>
                        </div>
                        <span class="icon-badge bg-primary-subtle text-primary"><i class="fa-solid fa-clipboard-check"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <div class="text-uppercase small text-muted">Average Score</div>
                            <span class="icon-badge bg-success-subtle text-success"><i class="fa-solid fa-percent"></i></span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="fs-3 fw-bold">{{ $avgScore }}%</div>
                        </div>
                        <div class="progress mt-2" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $avgScore }}%" aria-valuenow="{{ $avgScore }}" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase small text-muted">Current Rank</div>
                            <div class="fs-3 fw-bold">{{ $rankText }}</div>
                        </div>
                        <span class="icon-badge bg-info-subtle text-info"><i class="fa-solid fa-ranking-star"></i></span>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card stat-card h-100">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <div class="text-uppercase small text-muted">Last Activity</div>
                            <div class="fw-semibold">{{ $recentTests[0]['date'] ?? '—' }}</div>
                        </div>
                        <span class="icon-badge bg-warning-subtle text-warning"><i class="fa-regular fa-clock"></i></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Assessments Section -->
        <div class="row g-3 mb-4">
            <div class="col-12">
                <div class="card stat-card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="fw-semibold"><i class="fa-solid fa-clipboard-list me-2"></i>Available Assessments</span>
                        <a href="{{ route('student.assessments') }}" class="btn btn-sm btn-outline-primary">View All</a>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            @forelse($assessments as $assessment)
                                <div class="col-12 col-md-6 col-lg-4">
                                    <div class="card h-100 border">
                                        <div class="card-body d-flex flex-column">
                                            <div class="mb-3">
                                                <h6 class="card-title mb-2">{{ $assessment->name ?? 'Assessment' }}</h6>
                                                <p class="text-muted small mb-2">{{ $assessment->description ?? 'No description' }}</p>
                                                <div class="d-flex gap-2 mb-2">
                                                    <span class="badge bg-{{ ($assessment->category ?? '')==='Aptitude' ? 'primary' : 'success' }}">{{ $assessment->category ?? '' }}</span>
                                                    <span class="badge bg-info">{{ $assessment->time_limit ?? 0 }} min</span>
                                                </div>
                                            </div>
                                            <div class="mt-auto">
                                                @if(isset($attemptsByAssessment[$assessment->id]))
                                                    @php $attempt = $attemptsByAssessment[$assessment->id]; @endphp
                                                    <div class="mb-2">
                                                        <small class="text-muted">Score: {{ $attempt['score'] ?? 0 }}/{{ $attempt['total_questions'] ?? 0 }}</small>
                                                    </div>
                                                    <a href="{{ route('student.assessment.result', $assessment) }}" class="btn btn-outline-success w-100 btn-sm">
                                                        <i class="fas fa-eye me-1"></i>View Results & Review
                                                    </a>
                                                @else
                                                    <a href="{{ route('student.assessment.show', $assessment) }}" class="btn btn-primary w-100 btn-sm">Start Assessment</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center py-4">
                                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                                        <h6>No Assessments Available</h6>
                                        <p class="text-muted mb-0">Check back later for new assessments.</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-xl-8">
                <div class="card h-100 stat-card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="fw-semibold"><i class="fa-solid fa-chart-line me-2"></i>Performance Trend</span>
                        <small class="text-muted">Recent attempts</small>
                    </div>
                    <div class="card-body" style="min-height: 260px;">
                        <canvas id="performanceChart" height="120" style="display: none;"></canvas>
                        <div id="noChartData" class="text-center text-muted">No trend data available yet.</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-xl-4">
                <div class="card h-100 stat-card">
                    <div class="card-header fw-semibold"><i class="fa-regular fa-clock me-2"></i>Recent Tests</div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @forelse ($recentTests as $t)
                                <li class="list-group-item d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-3">
                                        <span class="icon-badge bg-primary-subtle text-primary"><i class="fa-solid fa-file-circle-check"></i></span>
                                        <div>
                                            <div class="fw-semibold">{{ $t['assessment_name'] ?? 'Assessment' }}</div>
                                            <small class="text-muted">{{ \Carbon\Carbon::parse($t['date'])->format('d M Y') }}</small>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold">{{ $t['score'] }}%</div>
                                        <div class="mt-1">
                                            @php $assessment = \App\Models\Assessment::find($t['assessment_id'] ?? 0); @endphp
                                            @if($assessment)
                                                <a href="{{ route('student.assessment.result', $assessment) }}" class="btn btn-sm btn-outline-secondary">View</a>
                                                <a href="{{ route('student.assessment.show', $assessment) }}" class="btn btn-sm btn-outline-secondary">Retake</a>
                                            @endif
                                        </div>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted">No recent tests found.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
        <script>
            (function(){
                const labels = {!! json_encode($trendLabels) !!};
                const scores = {!! json_encode($trendScores) !!};
                const canvas = document.getElementById('performanceChart');
                const noData = document.getElementById('noChartData');
                if (Array.isArray(labels) && labels.length && Array.isArray(scores) && scores.length) {
                    canvas.style.display = 'block';
                    noData.style.display = 'none';
                    const ctx = canvas.getContext('2d');
                    const gradient = ctx.createLinearGradient(0, 0, 0, 200);
                    gradient.addColorStop(0, 'rgba(13,110,253,0.35)');
                    gradient.addColorStop(1, 'rgba(13,110,253,0)');
                    new Chart(canvas, {
                        type: 'line',
                        data: {
                            labels: labels,
                            datasets: [{
                                label: 'Score %',
                                data: scores,
                                fill: true,
                                backgroundColor: gradient,
                                borderColor: 'rgba(13,110,253,1)',
                                tension: 0.35,
                                pointRadius: 3,
                                pointBackgroundColor: '#fff',
                                pointBorderColor: 'rgba(13,110,253,1)'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: { y: { beginAtZero: true, max: 100, ticks: { callback: (v) => v + '%' } } },
                            plugins: { legend: { display: false }, tooltip: { enabled: true, intersect: false, mode: 'index' } }
                        }
                    });
                }
            })();
        </script>
    </div>
</x-app-layout>