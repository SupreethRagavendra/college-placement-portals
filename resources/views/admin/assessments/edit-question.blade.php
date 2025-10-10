@extends('layouts.admin')

@section('title', 'Edit Question')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Edit Question</h1>
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
    <div class="card-header bg-warning">
        <h5 class="mb-0"><i class="fas fa-edit"></i> Edit Question</h5>
    </div>
    <div class="card-body">
        <form action="{{ route('admin.assessments.update-question', [$assessment, $question]) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="mb-3">
                <label for="question_text" class="form-label">Question *</label>
                <textarea class="form-control" id="question_text" name="question_text" rows="3" required>{{ old('question_text', $question->question_text) }}</textarea>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="option_a" class="form-label">Option A *</label>
                    <input type="text" class="form-control" id="option_a" name="option_a" value="{{ old('option_a', $question->options[0] ?? '') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="option_b" class="form-label">Option B *</label>
                    <input type="text" class="form-control" id="option_b" name="option_b" value="{{ old('option_b', $question->options[1] ?? '') }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="option_c" class="form-label">Option C *</label>
                    <input type="text" class="form-control" id="option_c" name="option_c" value="{{ old('option_c', $question->options[2] ?? '') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="option_d" class="form-label">Option D *</label>
                    <input type="text" class="form-control" id="option_d" name="option_d" value="{{ old('option_d', $question->options[3] ?? '') }}" required>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="correct_answer" class="form-label">Correct Answer *</label>
                    <select class="form-select" id="correct_answer" name="correct_answer" required>
                        <option value="">Select correct answer...</option>
                        <option value="A" {{ old('correct_answer', chr(65 + $question->correct_option)) == 'A' ? 'selected' : '' }}>A</option>
                        <option value="B" {{ old('correct_answer', chr(65 + $question->correct_option)) == 'B' ? 'selected' : '' }}>B</option>
                        <option value="C" {{ old('correct_answer', chr(65 + $question->correct_option)) == 'C' ? 'selected' : '' }}>C</option>
                        <option value="D" {{ old('correct_answer', chr(65 + $question->correct_option)) == 'D' ? 'selected' : '' }}>D</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="marks" class="form-label">Marks *</label>
                    <input type="number" class="form-control" id="marks" name="marks" value="{{ old('marks', $question->marks ?? 1) }}" min="1" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="difficulty_level" class="form-label">Difficulty Level *</label>
                    <select class="form-select" id="difficulty_level" name="difficulty_level" required>
                        <option value="easy" {{ old('difficulty_level', $question->difficulty_level) == 'easy' ? 'selected' : '' }}>Easy</option>
                        <option value="medium" {{ old('difficulty_level', $question->difficulty_level) == 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="hard" {{ old('difficulty_level', $question->difficulty_level) == 'hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>
            </div>

            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('admin.assessments.questions', $assessment) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Question
                </button>
            </div>
        </form>
    </div>
</div>
@endsection