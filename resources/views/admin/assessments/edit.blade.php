@extends('layouts.admin')

@section('title','Edit Assessment')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Edit Assessment</h1>
        <a class="btn btn-outline-secondary" href="{{ url('/admin/assessments') }}">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ url('/admin/assessments/' . ($assessment['id'] ?? '') . '/update') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Assessment Name</label>
                    <input class="form-control" name="name" value="{{ $assessment['name'] ?? '' }}" required />
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="3">{{ $assessment['description'] ?? '' }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category" required>
                        <option value="Aptitude" {{ ($assessment['category'] ?? '') === 'Aptitude' ? 'selected' : '' }}>Aptitude</option>
                        <option value="Technical" {{ ($assessment['category'] ?? '') === 'Technical' ? 'selected' : '' }}>Technical</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Time Limit (minutes)</label>
                    <input class="form-control" type="number" name="time_limit" min="1" max="300" value="{{ $assessment['time_limit'] ?? 30 }}" required />
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ ($assessment['is_active'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Update Assessment</button>
                    <a class="btn btn-outline-secondary" href="{{ url('/admin/assessments') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
