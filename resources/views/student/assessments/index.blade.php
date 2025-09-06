<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">Available Assessments</h2>
    </x-slot>
    <div class="container py-4">
        <div class="row g-3">
            @forelse($assessments as $assessment)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="mb-3">
                            <h5 class="card-title">{{ $assessment->name ?? 'Assessment' }}</h5>
                            <p class="text-muted mb-2">{{ $assessment->description ?? 'No description' }}</p>
                            <div class="d-flex gap-2 mb-2">
                                <span class="badge bg-{{ ($assessment->category ?? '')==='Aptitude' ? 'primary' : 'success' }}">{{ $assessment->category ?? '' }}</span>
                                <span class="badge bg-info">{{ $assessment->time_limit ?? 0 }} min</span>
                            </div>
                        </div>
                        <div class="mt-auto">
                            @if(isset($userResults[$assessment->id]))
                                @php $result = $userResults[$assessment->id]; @endphp
                                <div class="mb-2">
                                    <small class="text-muted">Score: {{ $result->score ?? 0 }}/{{ $result->total_questions ?? 0 }}</small>
                                </div>
                                <a href="{{ route('student.assessment.result', $assessment) }}" class="btn btn-outline-success w-100">
                                    <i class="fas fa-eye me-1"></i>View Results & Review
                                </a>
                            @else
                                <a href="{{ route('student.assessment.show', $assessment) }}" class="btn btn-primary w-100">Start Assessment</a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-12">
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                    <h5>No Assessments Available</h5>
                    <p class="text-muted">Check back later for new assessments.</p>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
