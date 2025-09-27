@extends('layouts.admin')

@section('title', 'Rejected Students')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Rejected Students</h1>
    <div class="badge bg-danger fs-6">
        {{ $rejectedStudents->total() }} rejected student{{ $rejectedStudents->total() !== 1 ? 's' : '' }}
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
            <i class="fas fa-times-circle text-danger me-2"></i>Rejected Students
        </h5>
        @if($rejectedStudents->count() > 0)
            <div class="btn-group" role="group">
                <a href="{{ route('admin.pending-students') }}" class="btn btn-sm btn-outline-warning">
                    <i class="fas fa-clock me-1"></i>View Pending
                </a>
                <a href="{{ route('admin.approved-students') }}" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-check-circle me-1"></i>View Approved
                </a>
            </div>
        @endif
    </div>
    <div class="card-body p-0">
        @if($rejectedStudents->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registration Date</th>
                            <th>Rejection Date</th>
                            <th>Rejection Reason</th>
                            <th width="120" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($rejectedStudents as $student)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-danger text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $student->name }}</h6>
                                            <small class="text-muted">Former Student</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-muted">{{ $student->email }}</span>
                                    @if($student->email_verified_at)
                                        <i class="fas fa-check-circle text-success ms-2" title="Email was verified"></i>
                                    @else
                                        <i class="fas fa-exclamation-triangle text-warning ms-2" title="Email was not verified"></i>
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <span class="text-muted">{{ $student->created_at->format('M d, Y') }}</span>
                                        <br>
                                        <small class="text-muted">{{ $student->created_at->diffForHumans() }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="text-danger">{{ $student->admin_rejected_at->format('M d, Y') }}</span>
                                        <br>
                                        <small class="text-muted">{{ $student->admin_rejected_at->format('H:i') }} ({{ $student->admin_rejected_at->diffForHumans() }})</small>
                                    </div>
                                </td>
                                <td>
                                    @if($student->rejection_reason)
                                        <span class="text-muted">{{ Str::limit($student->rejection_reason, 50) }}</span>
                                        @if(strlen($student->rejection_reason) > 50)
                                            <button class="btn btn-link btn-sm p-0 ms-2" onclick="showFullReason('{{ addslashes($student->rejection_reason) }}')" title="View full reason">
                                                <i class="fas fa-expand-alt"></i>
                                            </button>
                                        @endif
                                    @else
                                        <span class="text-muted fst-italic">No reason provided</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary" title="View Details" onclick="viewStudentDetails({{ $student->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-success" title="Restore Student" onclick="restoreStudent({{ $student->id }}, '{{ $student->name }}')">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fas fa-user-slash fa-3x text-muted mb-3"></i>
                <h5>No Rejected Students</h5>
                <p class="text-muted">No students have been rejected. All applications are either pending or approved.</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('admin.pending-students') }}" class="btn btn-warning">
                        <i class="fas fa-clock me-2"></i>View Pending
                    </a>
                    <a href="{{ route('admin.approved-students') }}" class="btn btn-success">
                        <i class="fas fa-check-circle me-2"></i>View Approved
                    </a>
                </div>
            </div>
        @endif
    </div>
    @if (method_exists($rejectedStudents, 'links') && $rejectedStudents->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-center">
                {{ $rejectedStudents->links() }}
            </div>
        </div>
    @endif
</div>

<!-- Student Details Modal -->
<div class="modal fade" id="studentDetailsModal" tabindex="-1" aria-labelledby="studentDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="studentDetailsModalLabel">
                    <i class="fas fa-user me-2"></i>Rejected Student Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="studentDetailsContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Reason Modal -->
<div class="modal fade" id="rejectionReasonModal" tabindex="-1" aria-labelledby="rejectionReasonModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="rejectionReasonModalLabel">
                    <i class="fas fa-info-circle me-2"></i>Rejection Reason
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="rejectionReasonContent">
                <!-- Content will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Restore Student Form -->
<form id="restoreForm" method="POST" style="display: none;">
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
    background-color: rgba(220, 53, 69, 0.05);
}

.btn-group .btn {
    border-radius: 6px;
}

.btn-group .btn:not(:last-child) {
    margin-right: 4px;
}
</style>

<script>
function viewStudentDetails(studentId) {
    fetch(`/admin/students/${studentId}/details`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                return;
            }
            
            const content = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-muted">Name</h6>
                        <p>${data.name}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Email</h6>
                        <p>${data.email}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Registration Date</h6>
                        <p>${data.created_at}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Email Verified</h6>
                        <p>${data.email_verified_at || 'Not verified'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Rejection Date</h6>
                        <p class="text-danger">${data.admin_rejected_at || 'Not rejected'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Status</h6>
                        <span class="badge bg-danger">${data.status}</span>
                    </div>
                </div>
            `;
            
            document.getElementById('studentDetailsContent').innerHTML = content;
            new bootstrap.Modal(document.getElementById('studentDetailsModal')).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load student details');
        });
}

function showFullReason(reason) {
    document.getElementById('rejectionReasonContent').innerHTML = `<p>${reason}</p>`;
    new bootstrap.Modal(document.getElementById('rejectionReasonModal')).show();
}

function restoreStudent(studentId, studentName) {
    if (confirm(`Are you sure you want to restore ${studentName}? This will recreate their account and set them as pending approval.`)) {
        const form = document.getElementById('restoreForm');
        form.action = `/admin/students/${studentId}/restore`;
        form.submit();
    }
}
</script>
@endpush