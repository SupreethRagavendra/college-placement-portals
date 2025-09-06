@extends('layouts.admin')

@section('title','Create Assessment')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="mb-0">Create Assessment</h1>
        <a class="btn btn-outline-secondary" href="{{ url('/admin/assessments') }}">Back</a>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ url('/admin/assessments/store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Assessment Name</label>
                    <input class="form-control" name="name" value="{{ old('name') }}" required />
                </div>
                <div class="mb-3">
                    <label class="form-label">Description</label>
                    <textarea class="form-control" name="description" rows="3">{{ old('description') }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category" required>
                        <option value="Aptitude">Aptitude</option>
                        <option value="Technical">Technical</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Time Limit (minutes)</label>
                    <input class="form-control" type="number" name="time_limit" min="1" max="300" value="{{ old('time_limit', 30) }}" required />
                </div>
                <div class="mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-primary" type="submit">Create Assessment</button>
                    <a class="btn btn-outline-secondary" href="{{ url('/admin/assessments') }}">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
