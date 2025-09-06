@extends('layouts.admin')

@section('title','Edit Question')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Edit Question</h1>
        <a class="btn btn-outline-secondary" href="{{ url('/admin/questions') }}">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ url('/admin/questions/' . ($question['id'] ?? '') . '/update') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category" required>
                        <option value="Aptitude" {{ ($question['category'] ?? '') === 'Aptitude' ? 'selected' : '' }}>Aptitude</option>
                        <option value="Technical" {{ ($question['category'] ?? '') === 'Technical' ? 'selected' : '' }}>Technical</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Question Text</label>
                    <textarea class="form-control" name="question_text" rows="3" required>{{ $question['question_text'] ?? '' }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Option A</label>
                        <input class="form-control" name="option_a" value="{{ $question['option_a'] ?? '' }}" required />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Option B</label>
                        <input class="form-control" name="option_b" value="{{ $question['option_b'] ?? '' }}" required />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Option C</label>
                        <input class="form-control" name="option_c" value="{{ $question['option_c'] ?? '' }}" required />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Option D</label>
                        <input class="form-control" name="option_d" value="{{ $question['option_d'] ?? '' }}" required />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Correct Answer</label>
                    <select class="form-select" name="correct_answer" required>
                        <option value="A" {{ ($question['correct_answer'] ?? '') === 'A' ? 'selected' : '' }}>A</option>
                        <option value="B" {{ ($question['correct_answer'] ?? '') === 'B' ? 'selected' : '' }}>B</option>
                        <option value="C" {{ ($question['correct_answer'] ?? '') === 'C' ? 'selected' : '' }}>C</option>
                        <option value="D" {{ ($question['correct_answer'] ?? '') === 'D' ? 'selected' : '' }}>D</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Difficulty</label>
                    <select class="form-select" name="difficulty">
                        <option value="" {{ empty($question['difficulty'] ?? '') ? 'selected' : '' }}>Select</option>
                        <option value="Easy" {{ ($question['difficulty'] ?? '') === 'Easy' ? 'selected' : '' }}>Easy</option>
                        <option value="Medium" {{ ($question['difficulty'] ?? '') === 'Medium' ? 'selected' : '' }}>Medium</option>
                        <option value="Hard" {{ ($question['difficulty'] ?? '') === 'Hard' ? 'selected' : '' }}>Hard</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Update</button>
                    <a class="btn btn-outline-secondary" href="{{ url('/admin/questions') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection


