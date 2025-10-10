@extends('layouts.admin')

@section('title', 'Edit Assessment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Edit Assessment</h1>
    <a href="{{ route('admin.assessments.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Assessments
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
    <div class="card-body">
        <form action="{{ route('admin.assessments.update', $assessment) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" value="{{ old('title', $assessment->title) }}" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="category" class="form-label">Category *</label>
                        <select class="form-control" id="category" name="category" required>
                            <option value="">Select Category</option>
                            <option value="Aptitude" {{ old('category', $assessment->category) == 'Aptitude' ? 'selected' : '' }}>Aptitude</option>
                            <option value="Technical" {{ old('category', $assessment->category) == 'Technical' ? 'selected' : '' }}>Technical</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="3">{{ old('description', $assessment->description) }}</textarea>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="duration" class="form-label">Duration (minutes) *</label>
                        <input type="number" class="form-control" id="duration" name="duration" value="{{ old('duration', $assessment->duration) }}" min="1" max="300" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="total_marks" class="form-label">Total Marks *</label>
                        <input type="number" class="form-control" id="total_marks" name="total_marks" value="{{ old('total_marks', $assessment->total_marks) }}" min="1" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="pass_percentage" class="form-label">Pass Percentage *</label>
                        <input type="number" class="form-control" id="pass_percentage" name="pass_percentage" value="{{ old('pass_percentage', $assessment->pass_percentage) }}" min="1" max="100" required>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="difficulty_level" class="form-label">Difficulty Level *</label>
{{ ... }}
                            <option value="easy" {{ old('difficulty_level', $assessment->difficulty_level) == 'easy' ? 'selected' : '' }}>Easy</option>
                            <option value="medium" {{ old('difficulty_level', $assessment->difficulty_level) == 'medium' ? 'selected' : '' }}>Medium</option>
                            <option value="hard" {{ old('difficulty_level', $assessment->difficulty_level) == 'hard' ? 'selected' : '' }}>Hard</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mb-3">
                        <label for="status" class="form-label">Status *</label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="draft" {{ old('status', $assessment->status) == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="active" {{ old('status', $assessment->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $assessment->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="allow_multiple_attempts" name="allow_multiple_attempts" {{ old('allow_multiple_attempts', $assessment->allow_multiple_attempts) ? 'checked' : '' }}>
                    <label class="form-check-label" for="allow_multiple_attempts">
                        Allow Multiple Attempts
                    </label>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="show_results_immediately" name="show_results_immediately" {{ old('show_results_immediately', $assessment->show_results_immediately) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_results_immediately">
                        Show Results Immediately After Submission
                    </label>
                </div>
            </div>
            
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="show_correct_answers" name="show_correct_answers" {{ old('show_correct_answers', $assessment->show_correct_answers) ? 'checked' : '' }}>
                    <label class="form-check-label" for="show_correct_answers">
                        Show Correct Answers in Results
                    </label>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="{{ route('admin.assessments.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">Update Assessment</button>
            </div>
        </form>
    </div>
</div>
@endsection