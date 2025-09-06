<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">Assessment Result</h2>
    </x-slot>
    
    <style>
        .bg-light-success {
            background-color: rgba(25, 135, 84, 0.1) !important;
        }
        .bg-light-danger {
            background-color: rgba(220, 53, 69, 0.1) !important;
        }
        .question-review {
            transition: all 0.3s ease;
        }
        .question-review:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
    </style>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8">
                <div class="card">
                    <div class="card-body text-center">
                        <h3 class="mb-2">{{ $assessment->name ?? 'Assessment' }}</h3>
                        <div class="mb-4">
                            <h1 class="display-4 text-primary">{{ $result->score ?? 0 }}/{{ $result->total_questions ?? 0 }}</h1>
                            <p class="text-muted">Score: {{ $result->total_questions > 0 ? round(($result->score / $result->total_questions) * 100, 1) : 0 }}%</p>
                        </div>
                        <div class="row text-center mb-4">
                            <div class="col-4">
                                <div class="border-end">
                                    <h5 class="text-success">{{ $result->score ?? 0 }}</h5>
                                    <small class="text-muted">Correct</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border-end">
                                    <h5 class="text-danger">{{ ($result->total_questions ?? 0) - ($result->score ?? 0) }}</h5>
                                    <small class="text-muted">Incorrect</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <h5 class="text-info">{{ $result->submitted_at ? $result->submitted_at->format('Y-m-d H:i:s') : 'N/A' }}</h5>
                                <small class="text-muted">Completed At</small>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-center mb-4">
                            <a href="{{ route('student.assessments') }}" class="btn btn-primary">Back to Assessments</a>
                            <a href="{{ route('student.assessment.history') }}" class="btn btn-outline-secondary">View History</a>
                        </div>
                    </div>
                </div>

                <!-- Detailed Review Section -->
                @if(!empty($detailedResults))
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Detailed Review - Correct Answers</h5>
                        <small class="text-muted">Review your answers and learn from the correct solutions</small>
                    </div>
                    <div class="card-body">
                        @foreach($detailedResults as $index => $detail)
                        <div class="question-review mb-4 p-3 border rounded {{ $detail['is_correct'] ? 'border-success bg-light-success' : 'border-danger bg-light-danger' }}">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h6 class="mb-0">
                                    <span class="badge {{ $detail['is_correct'] ? 'bg-success' : 'bg-danger' }} me-2">
                                        {{ $detail['is_correct'] ? '✓ Correct' : '✗ Incorrect' }}
                                    </span>
                                    Question {{ $index + 1 }}
                                </h6>
                            </div>
                            
                            <div class="question-text mb-3">
                                <strong>{{ $detail['question']->question }}</strong>
                            </div>
                            
                            <div class="options">
                                @php 
                                    $options = is_array($detail['question']->options) ? $detail['question']->options : json_decode($detail['question']->options, true) ?? [];
                                    $letters = ['A', 'B', 'C', 'D'];
                                @endphp
                                
                                @foreach($options as $optIndex => $option)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" disabled 
                                           {{ $optIndex == $detail['user_answer'] ? 'checked' : '' }}>
                                    <label class="form-check-label d-flex align-items-center">
                                        <span class="me-2">{{ $letters[$optIndex] ?? $optIndex }}.</span>
                                        <span class="{{ $optIndex == $detail['correct_answer'] ? 'fw-bold text-success' : ($optIndex == $detail['user_answer'] && !$detail['is_correct'] ? 'text-danger' : '') }}">
                                            {{ $option }}
                                            @if($optIndex == $detail['correct_answer'])
                                                <i class="fas fa-check-circle text-success ms-2"></i>
                                            @elseif($optIndex == $detail['user_answer'] && !$detail['is_correct'])
                                                <i class="fas fa-times-circle text-danger ms-2"></i>
                                            @endif
                                        </span>
                                    </label>
                                </div>
                                @endforeach
                            </div>
                            
                            @if(!$detail['is_correct'])
                            <div class="alert alert-info mt-3 mb-0">
                                <small>
                                    <i class="fas fa-info-circle me-1"></i>
                                    <strong>Correct Answer:</strong> {{ $letters[$detail['correct_answer']] ?? $detail['correct_answer'] }} - {{ $options[$detail['correct_answer']] ?? 'N/A' }}
                                </small>
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
