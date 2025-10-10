@extends('layouts.admin')

@section('title', 'Assessments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.assessments.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create Assessment
    </a>
</div>

@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('status') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.assessments.index') }}">
            <div class="row">
                <div class="col-md-3">
                    <input type="text" name="search" class="form-control" placeholder="Search by title" value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <option value="Aptitude" {{ request('category') == 'Aptitude' ? 'selected' : '' }}>Aptitude</option>
                        <option value="Technical" {{ request('category') == 'Technical' ? 'selected' : '' }}>Technical</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="{{ route('admin.assessments.index') }}" class="btn btn-secondary">Clear</a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Assessments Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Questions</th>
                    <th>Created At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($assessments as $assessment)
                <tr>
                    <td>
                        <strong>{{ $assessment->title }}</strong>
                        @if($assessment->description)
                            <br><small class="text-muted">{{ Str::limit($assessment->description, 50) }}</small>
                        @endif
                    </td>
                    <td>
                        <span class="badge bg-info">{{ $assessment->category }}</span>
                    </td>
                    <td>{{ $assessment->duration }} min</td>
                    <td>
                        @if($assessment->status == 'active')
                            <span class="badge bg-success">Active</span>
                        @elseif($assessment->status == 'inactive')
                            <span class="badge bg-secondary">Inactive</span>
                        @else
                            <span class="badge bg-warning">Draft</span>
                        @endif
                    </td>
                    <td>
                        @if($assessment->questions_count > 0)
                            <span class="badge bg-primary">{{ $assessment->questions_count }} questions</span>
                        @else
                            <span class="badge bg-danger">No questions</span>
                        @endif
                    </td>
                    <td>{{ $assessment->created_at->format('M d, Y') }}</td>
                    <td>
                        <div class="btn-group" role="group">
                            <a href="{{ route('admin.assessments.show', $assessment) }}" class="btn btn-sm btn-outline-primary" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.assessments.edit', $assessment) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <a href="{{ route('admin.assessments.questions', $assessment) }}" class="btn btn-sm btn-outline-info" title="Questions">
                                <i class="fas fa-question-circle"></i>
                            </a>
                            <a href="{{ route('admin.assessments.add-question', $assessment) }}" class="btn btn-sm btn-outline-primary" title="Add Question">
                                <i class="fas fa-plus-circle"></i>
                            </a>
                            <a href="{{ route('admin.results.index', $assessment) }}" class="btn btn-sm btn-outline-success" title="Results">
                                <i class="fas fa-chart-bar"></i>
                            </a>
                            @if($assessment->status == 'active')
                            <form action="{{ route('admin.assessments.toggle-status', $assessment) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="inactive">
                                <button type="submit" class="btn btn-sm btn-outline-warning" title="Deactivate" onclick="return confirm('Are you sure you want to deactivate this assessment?');">
                                    <i class="fas fa-pause"></i>
                                </button>
                            </form>
                            @else
                            <form action="{{ route('admin.assessments.toggle-status', $assessment) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="active">
                                <button type="submit" class="btn btn-sm btn-outline-success" title="Activate" onclick="return confirm('Are you sure you want to activate this assessment?');">
                                    <i class="fas fa-play"></i>
                                </button>
                            </form>
                            @endif
                            <form action="{{ route('admin.assessments.destroy', $assessment) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this assessment?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            <form action="{{ route('admin.assessments.duplicate', $assessment) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-outline-dark" title="Duplicate">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No assessments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer">
        {{ $assessments->links() }}
    </div>
</div>
@endsection

@section('styles')
<style>
.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1) !important;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.badge {
    font-size: 0.75rem;
}

.gap-1 {
    gap: 0.25rem !important;
}

.gap-2 {
    gap: 0.5rem !important;
}

.btn-group .btn {
    border-radius: 0.375rem;
}

.btn-group .btn:not(:last-child) {
    border-top-right-radius: 0;
    border-bottom-right-radius: 0;
}

.btn-group .btn:not(:first-child) {
    border-top-left-radius: 0;
    border-bottom-left-radius: 0;
}
</style>
@endsection

@section('scripts')
<script>
// Event delegation for toggle buttons
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection