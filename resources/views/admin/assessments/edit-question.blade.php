@extends('layouts.admin')

@section('title','Edit Question')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="mb-0">Edit Question</h1>
            <p class="text-muted mb-0">{{ $assessment['name'] ?? 'Assessment' }} • {{ $assessment['category'] ?? '' }}</p>
        </div>
        <a class="btn btn-outline-secondary" href="{{ url('/admin/assessments/' . ($assessment['id'] ?? '') . '/questions') }}">Back</a>
    </div>
    
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ url('/admin/assessments/' . ($assessment['id'] ?? '') . '/questions/' . ($question['id'] ?? '') . '/update') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Question Text</label>
                    <textarea class="form-control" name="question_text" rows="3" required>{{ old('question_text', $question['question'] ?? '') }}</textarea>
                    @error('question_text')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Option A</label>
                        <input class="form-control" name="option_a" value="{{ old('option_a', $question['options'][0] ?? '') }}" required />
                        @error('option_a')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Option B</label>
                        <input class="form-control" name="option_b" value="{{ old('option_b', $question['options'][1] ?? '') }}" required />
                        @error('option_b')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Option C</label>
                        <input class="form-control" name="option_c" value="{{ old('option_c', $question['options'][2] ?? '') }}" required />
                        @error('option_c')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Option D</label>
                        <input class="form-control" name="option_d" value="{{ old('option_d', $question['options'][3] ?? '') }}" required />
                        @error('option_d')
                            <div class="text-danger">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Correct Answer</label>
                    <select class="form-select" name="correct_answer" required>
                        <option value="">Select Correct Answer</option>
                        @php
                            $correctOption = $question['correct_option'] ?? '';
                            $correctLetter = ['0' => 'A', '1' => 'B', '2' => 'C', '3' => 'D'][$correctOption] ?? '';
                        @endphp
                        <option value="A" {{ old('correct_answer', $correctLetter) == 'A' ? 'selected' : '' }}>A</option>
                        <option value="B" {{ old('correct_answer', $correctLetter) == 'B' ? 'selected' : '' }}>B</option>
                        <option value="C" {{ old('correct_answer', $correctLetter) == 'C' ? 'selected' : '' }}>C</option>
                        <option value="D" {{ old('correct_answer', $correctLetter) == 'D' ? 'selected' : '' }}>D</option>
                    </select>
                    @error('correct_answer')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">Difficulty</label>
                    <select class="form-select" name="difficulty">
                        <option value="">Select Difficulty</option>
                        <option value="Easy" {{ old('difficulty', $question['difficulty'] ?? '') == 'Easy' ? 'selected' : '' }}>Easy</option>
                        <option value="Medium" {{ old('difficulty', $question['difficulty'] ?? '') == 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="Hard" {{ old('difficulty', $question['difficulty'] ?? '') == 'Hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                    @error('difficulty')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Update Question</button>
                    <a class="btn btn-outline-secondary" href="{{ url('/admin/assessments/' . ($assessment['id'] ?? '') . '/questions') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
