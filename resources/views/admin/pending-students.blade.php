@extends('layouts.admin')

@section('title', 'Pending Students')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Pending Student Approvals</h1>
    <div class="badge bg-warning text-dark fs-6">
        {{ $pendingStudents->total() }} pending approval{{ $pendingStudents->total() !== 1 ? 's' : '' }}
    </div>
</div>

@if (session('status'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>{{ $errors->first() }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h5 class="card-title mb-0">
            <i class="fas fa-clock text-warning me-2"></i>Students Awaiting Approval
        </h5>
        @if($pendingStudents->count() > 0)
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-success" onclick="selectAll()">
                    <i class="fas fa-check-square me-1"></i>Select All
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                    <i class="fas fa-square me-1"></i>Clear
                </button>
            </div>
        @endif
    </div>
    <div class="card-body p-0">
        @if($pendingStudents->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                            </th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registered At</th>
                            <th width="200" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingStudents as $student)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input student-checkbox" value="{{ $student->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $student->name }}</h6>
                                            <small class="text-muted">Student</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $student->email }}</span>
                                    @if($student->email_verified_at)
                                        <i class="fas fa-check-circle text-success ms-2" title="Email verified"></i>
                                    @else
                                        <i class="fas fa-exclamation-triangle text-warning ms-2" title="Email not verified"></i>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <span class="text-muted">{{ $student->created_at->format('M d, Y') }}</span>
                                        <br>
                                        <small class="text-muted">{{ $student->created_at->format('H:i') }} ({{ $student->created_at->diffForHumans() }})</small>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <form action="{{ route('admin.approve-student', $student->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success" title="Approve Student" onclick="return confirm('Are you sure you want to approve {{ $student->name }}?')">
                                                <i class="fas fa-check me-1"></i>Approve
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.reject-student', $student->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-danger" title="Reject Student" onclick="return confirm('Are you sure you want to reject {{ $student->name }}? This will permanently delete their account.')">
                                                <i class="fas fa-times me-1"></i>Reject
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($pendingStudents->count() > 1)
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted">Selected: <span id="selectedCount">0</span> students</span>
                        </div>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-success" id="bulkApproveBtn" disabled onclick="bulkApprove()">
                                <i class="fas fa-check me-1"></i>Approve Selected
                            </button>
                            <button type="button" class="btn btn-danger" id="bulkRejectBtn" disabled onclick="bulkReject()">
                                <i class="fas fa-times me-1"></i>Reject Selected
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                <h5>No Pending Students</h5>
                <p class="text-muted">All students have been processed! New registrations will appear here.</p>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-primary">
                    <i class="fas fa-tachometer-alt me-2"></i>Back to Dashboard
                </a>
            </div>
        @endif
    </div>
    @if (method_exists($pendingStudents, 'links') && $pendingStudents->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-center">
                {{ $pendingStudents->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Bulk Action Forms -->
<form id="bulkApproveForm" action="{{ route('admin.bulk-approve') }}" method="POST" style="display: none;">
    @csrf
</form>

<form id="bulkRejectForm" action="{{ route('admin.bulk-reject') }}" method="POST" style="display: none;">
    @csrf
</form>
@endsection

@push('scripts')
<style>
.avatar-sm {
    width: 36px;
    height: 36px;
    font-weight: 600;
    font-size: 14px;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #6c757d;
    background-color: #f8f9fa;
}

.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}

.btn-group .btn {
    border-radius: 6px;
}

.btn-group .btn:not(:last-child) {
    margin-right: 4px;
}
</style>

<script>
function selectAll() {
    const checkboxes = document.querySelectorAll('.student-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    selectAllCheckbox.checked = true;
    updateSelectedCount();
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.student-checkbox');
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    selectAllCheckbox.checked = false;
    updateSelectedCount();
}

function updateSelectedCount() {
    const selectedCheckboxes = document.querySelectorAll('.student-checkbox:checked');
    const count = selectedCheckboxes.length;
    
    document.getElementById('selectedCount').textContent = count;
    
    const bulkApproveBtn = document.getElementById('bulkApproveBtn');
    const bulkRejectBtn = document.getElementById('bulkRejectBtn');
    
    if (bulkApproveBtn) bulkApproveBtn.disabled = count === 0;
    if (bulkRejectBtn) bulkRejectBtn.disabled = count === 0;
}

function getSelectedStudentIds() {
    const selectedCheckboxes = document.querySelectorAll('.student-checkbox:checked');
    return Array.from(selectedCheckboxes).map(checkbox => checkbox.value);
}

function bulkApprove() {
    const selectedIds = getSelectedStudentIds();
    if (selectedIds.length === 0) {
        alert('Please select at least one student to approve.');
        return;
    }
    
    if (confirm(`Are you sure you want to approve ${selectedIds.length} student(s)?`)) {
        const form = document.getElementById('bulkApproveForm');
        
        // Add student IDs to form
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'student_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        form.submit();
    }
}

function bulkReject() {
    const selectedIds = getSelectedStudentIds();
    if (selectedIds.length === 0) {
        alert('Please select at least one student to reject.');
        return;
    }
    
    if (confirm(`Are you sure you want to reject ${selectedIds.length} student(s)? This action cannot be undone.`)) {
        const form = document.getElementById('bulkRejectForm');
        
        // Add student IDs to form
        selectedIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'student_ids[]';
            input.value = id;
            form.appendChild(input);
        });
        
        form.submit();
    }
}

// Initialize event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Handle select all checkbox
    const selectAllCheckbox = document.getElementById('selectAllCheckbox');
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.student-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });
    }
    
    // Handle individual checkboxes
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');
    studentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();
            
            // Update select all checkbox state
            const totalCheckboxes = studentCheckboxes.length;
            const checkedCheckboxes = document.querySelectorAll('.student-checkbox:checked').length;
            
            if (selectAllCheckbox) {
                selectAllCheckbox.checked = totalCheckboxes === checkedCheckboxes;
                selectAllCheckbox.indeterminate = checkedCheckboxes > 0 && checkedCheckboxes < totalCheckboxes;
            }
        });
    });
    
    // Initialize count
    updateSelectedCount();
});
</script>
@endpush