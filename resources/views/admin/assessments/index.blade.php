@extends('layouts.admin')

@section('title','Assessments')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="mb-0">Assessments</h1>
            <p class="text-muted mb-0">Manage assessments and their questions</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.questions.create') }}" class="btn btn-outline-success">
                <i class="fas fa-question-circle me-2"></i>Add Question
            </a>
            <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-info">
                <i class="fas fa-list me-2"></i>All Questions
            </a>
            <a href="{{ route('admin.assessments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create Assessment
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px">ID</th>
                        <th>Name</th>
                        <th style="width: 120px">Category</th>
                        <th style="width: 100px">Time Limit</th>
                        <th style="width: 100px">Questions</th>
                        <th style="width: 100px">Status</th>
                        <th style="width: 300px" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($assessments as $assessment)
                    <tr>
                        <td>{{ $assessment['id'] ?? '' }}</td>
                        <td>
                            <div>
                                <strong>{{ $assessment['name'] ?? '' }}</strong>
                                @if(!empty($assessment['description']))
                                    <br><small class="text-muted">{{ Str::limit($assessment['description'], 100) }}</small>
                                @endif
                            </div>
                        </td>
                        <td><span class="badge bg-{{ ($assessment['category'] ?? '')==='Aptitude' ? 'primary' : 'success' }}">{{ $assessment['category'] ?? '' }}</span></td>
                        <td>{{ $assessment['time_limit'] ?? 0 }} min</td>
                        <td><span class="badge bg-info">{{ $assessment['question_count'] ?? 0 }}</span></td>
                        <td>
                            @if($assessment['is_active'] ?? false)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-secondary">Inactive</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <div class="d-flex flex-wrap gap-1 justify-content-end">
                                <!-- Question Management -->
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.assessments.questions', $assessment['id'] ?? '') }}" 
                                       class="btn btn-outline-primary" title="View Questions">
                                        <i class="fas fa-list"></i>
                                    </a>
                                    <a href="{{ route('admin.assessments.add-question', $assessment['id'] ?? '') }}" 
                                       class="btn btn-outline-success" title="Add Question">
                                        <i class="fas fa-plus"></i>
                                    </a>
                                </div>
                                
                                <!-- Assessment Management -->
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.assessments.edit', $assessment['id'] ?? '') }}" 
                                       class="btn btn-outline-secondary" title="Edit Assessment">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button class="btn btn-outline-info" 
                                            onclick="toggleAssessmentStatus({{ $assessment['id'] ?? '' }}, {{ ($assessment['is_active'] ?? false) ? 'false' : 'true' }})"
                                            title="{{ ($assessment['is_active'] ?? false) ? 'Deactivate' : 'Activate' }}">
                                        <i class="fas fa-{{ ($assessment['is_active'] ?? false) ? 'pause' : 'play' }}"></i>
                                    </button>
                                </div>
                                
                                <!-- Delete -->
                                <form action="{{ route('admin.assessments.destroy', $assessment['id'] ?? '') }}" 
                                      method="POST" class="d-inline" 
                                      onsubmit="return confirm('Are you sure you want to delete this assessment? This action cannot be undone.');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" title="Delete Assessment">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center p-4">
                            <div class="py-5">
                                <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                                <h5>No Assessments Found</h5>
                                <p class="text-muted">Create your first assessment to get started!</p>
                                <a href="{{ route('admin.assessments.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus me-2"></i>Create Assessment
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions Card -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-question-circle me-2"></i>Quick Question Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.questions.create') }}" class="btn btn-outline-success">
                            <i class="fas fa-plus me-2"></i>Create New Question
                        </a>
                        <a href="{{ route('admin.questions.import') }}" class="btn btn-outline-info">
                            <i class="fas fa-upload me-2"></i>Import Questions
                        </a>
                        <a href="{{ route('admin.questions.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-list me-2"></i>Manage All Questions
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>Assessment Analytics
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-primary">
                            <i class="fas fa-chart-bar me-2"></i>View Reports
                        </a>
                        <a href="{{ route('admin.reports.student-performance') }}" class="btn btn-outline-info">
                            <i class="fas fa-user-graduate me-2"></i>Student Progress
                        </a>
                        <a href="{{ route('admin.reports.export') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-download me-2"></i>Export Data
                        </a>
                    </div>
                </div>
            </div>
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
function toggleAssessmentStatus(assessmentId, newStatus) {
    const action = newStatus === 'true' ? 'activate' : 'deactivate';
    const statusText = newStatus === 'true' ? 'activate' : 'deactivate';
    
    if (confirm(`Are you sure you want to ${statusText} this assessment?`)) {
        fetch(`/admin/assessments/${assessmentId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                is_active: newStatus === 'true'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error updating assessment status: ' + (data.message || 'Unknown error'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the assessment status');
        });
    }
}

// Add tooltip functionality
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[title]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>
@endsection
