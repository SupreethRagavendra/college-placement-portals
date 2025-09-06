@extends('layouts.admin')

@section('title','Add Question')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Add Question</h1>
        <a class="btn btn-outline-secondary" href="{{ url('/admin/questions') }}">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ url('/admin/questions/store') }}">
                @csrf
                @if(count($assessments) > 0)
                <div class="mb-3">
                    <label class="form-label">Assessment</label>
                    <select class="form-select" name="assessment_id" required>
                        <option value="">Select Assessment</option>
                        @foreach($assessments as $assessment)
                            <option value="{{ $assessment['id'] ?? '' }}">{{ $assessment['name'] ?? '' }} ({{ $assessment['category'] ?? '' }})</option>
                        @endforeach
                    </select>
                </div>
                @else
                <div class="alert alert-info">
                    <strong>Note:</strong> No assessments are available. Questions will be created without being linked to a specific assessment. 
                    <a href="{{ url('/admin/assessments') }}" class="alert-link">Create an assessment first</a> to organize questions.
                </div>
                @endif
                <div class="mb-3">
                    <label class="form-label">Question Text</label>
                    <textarea class="form-control" name="question_text" rows="3" required>{{ old('question_text') }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Option A</label>
                        <input class="form-control" name="option_a" value="{{ old('option_a') }}" required />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Option B</label>
                        <input class="form-control" name="option_b" value="{{ old('option_b') }}" required />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Option C</label>
                        <input class="form-control" name="option_c" value="{{ old('option_c') }}" required />
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Option D</label>
                        <input class="form-control" name="option_d" value="{{ old('option_d') }}" required />
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Correct Answer</label>
                    <select class="form-select" name="correct_answer" required>
                        <option value="A">A</option>
                        <option value="B">B</option>
                        <option value="C">C</option>
                        <option value="D">D</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Difficulty</label>
                    <select class="form-select" name="difficulty">
                        <option value="">Select</option>
                        <option value="Easy">Easy</option>
                        <option value="Medium">Medium</option>
                        <option value="Hard">Hard</option>
                    </select>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Save</button>
                    <a class="btn btn-outline-secondary" href="{{ url('/admin/questions') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection


