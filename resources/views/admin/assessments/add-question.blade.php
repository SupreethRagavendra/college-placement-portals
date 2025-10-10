@extends('layouts.admin')

@section('title', 'Add Question')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Add New Question</h1>
        <p class="text-muted mb-0">{{ $assessment->title }}</p>
    </div>
    <a href="{{ route('admin.assessments.questions', $assessment) }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Questions
    </a>
</div>

@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fas fa-plus-circle"></i> Create New Question</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.assessments.store-question', $assessment) }}" method="POST">
            @csrf
            
            <div class="mb-3">
                <label for="question_text" class="form-label">Question *</label>
                <textarea class="form-control" id="question_text" name="question_text" rows="3" required>{{ old('question_text') }}</textarea>
                <small class="text-muted">Enter the question text clearly and concisely</small>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="option_a" class="form-label">Option A *</label>
                    <input type="text" class="form-control" id="option_a" name="option_a" value="{{ old('option_a') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="option_b" class="form-label">Option B *</label>
                    <input type="text" class="form-control" id="option_b" name="option_b" value="{{ old('option_b') }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="option_c" class="form-label">Option C *</label>
                    <input type="text" class="form-control" id="option_c" name="option_c" value="{{ old('option_c') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="option_d" class="form-label">Option D *</label>
                    <input type="text" class="form-control" id="option_d" name="option_d" value="{{ old('option_d') }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="correct_answer" class="form-label">Correct Answer *</label>
                    <select class="form-select" id="correct_answer" name="correct_answer" required>
                        <option value="">Select correct answer...</option>
                        <option value="A" {{ old('correct_answer') == 'A' ? 'selected' : '' }}>A</option>
                        <option value="B" {{ old('correct_answer') == 'B' ? 'selected' : '' }}>B</option>
                        <option value="C" {{ old('correct_answer') == 'C' ? 'selected' : '' }}>C</option>
                        <option value="D" {{ old('correct_answer') == 'D' ? 'selected' : '' }}>D</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="marks" class="form-label">Marks *</label>
                    <input type="number" class="form-control" id="marks" name="marks" value="{{ old('marks', 1) }}" min="1" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="difficulty_level" class="form-label">Difficulty Level *</label>
                    <select class="form-select" id="difficulty_level" name="difficulty_level" required>
                        <option value="easy" {{ old('difficulty_level') == 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ old('difficulty_level', 'medium') == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ old('difficulty_level') == 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('admin.assessments.questions', $assessment) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Add Question
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Preview Card -->
<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-eye"></i> Live Preview</h5>
    </div>
    <div class="card-body">
        <p id="preview-question" class="fw-bold">Your question will appear here...</p>
        <div id="preview-options" class="ms-3">
            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" disabled>
                <label class="form-check-label"><strong>A.</strong> <span id="preview-option-a">Option A</span></label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" disabled>
                <label class="form-check-label"><strong>B.</strong> <span id="preview-option-b">Option B</span></label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" disabled>
                <label class="form-check-label"><strong>C.</strong> <span id="preview-option-c">Option C</span></label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" disabled>
                <label class="form-check-label"><strong>D.</strong> <span id="preview-option-d">Option D</span></label>
            </div>
        </div>
        <p class="mt-3 mb-0">
            <span class="badge bg-success" id="preview-marks">1 mark</span>
            <span class="badge bg-warning" id="preview-difficulty">Medium</span>
        </p>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Live preview functionality
    const questionInput = document.getElementById('question_text');
    const optionAInput = document.getElementById('option_a');
    const optionBInput = document.getElementById('option_b');
    const optionCInput = document.getElementById('option_c');
    const optionDInput = document.getElementById('option_d');
    const marksInput = document.getElementById('marks');
    const difficultyInput = document.getElementById('difficulty_level');

    function updatePreview() {
        document.getElementById('preview-question').textContent = questionInput.value || 'Your question will appear here...';
        document.getElementById('preview-option-a').textContent = optionAInput.value || 'Option A';
        document.getElementById('preview-option-b').textContent = optionBInput.value || 'Option B';
        document.getElementById('preview-option-c').textContent = optionCInput.value || 'Option C';
        document.getElementById('preview-option-d').textContent = optionDInput.value || 'Option D';
        
        const marks = marksInput.value || 1;
        document.getElementById('preview-marks').textContent = marks + (marks == 1 ? ' mark' : ' marks');
        
        const difficulty = difficultyInput.value || 'medium';
        const difficultyBadge = document.getElementById('preview-difficulty');
        difficultyBadge.textContent = difficulty.charAt(0).toUpperCase() + difficulty.slice(1);
        difficultyBadge.className = 'badge bg-' + (difficulty == 'easy' ? 'success' : (difficulty == 'hard' ? 'danger' : 'warning'));
    }

    questionInput.addEventListener('input', updatePreview);
    optionAInput.addEventListener('input', updatePreview);
    optionBInput.addEventListener('input', updatePreview);
    optionCInput.addEventListener('input', updatePreview);
    optionDInput.addEventListener('input', updatePreview);
    marksInput.addEventListener('input', updatePreview);
    difficultyInput.addEventListener('change', updatePreview);

    // Initial preview
    updatePreview();
});
</script>
@endsection