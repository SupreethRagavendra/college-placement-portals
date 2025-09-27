@extends('layouts.admin')

@section('title', 'Approved Students')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Approved Students</h1>
    <div class="badge bg-success fs-6">
        {{ $approvedStudents->total() }} approved student{{ $approvedStudents->total() !== 1 ? 's' : '' }}
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
            <i class="fas fa-check-circle text-success me-2"></i>Approved Students
        </h5>
        @if($approvedStudents->count() > 0)
            <div class="btn-group" role="group">
                <a href="{{ route('admin.pending-students') }}" class="btn btn-sm btn-outline-warning">
                    <i class="fas fa-clock me-1"></i>View Pending
                </a>
                <a href="{{ route('admin.rejected-students') }}" class="btn btn-sm btn-outline-danger">
                    <i class="fas fa-times-circle me-1"></i>View Rejected
                </a>
            </div>
        @endif
    </div>
    <div class="card-body p-0">
        @if($approvedStudents->count() > 0)
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Approved At</th>
                            <th>Approved By</th>
                            <th width="120" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approvedStudents as $student)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3">
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
                                    @endif
                                </td>
                                <td>
                                    <div>
                                        <span class="text-muted">{{ $student->admin_approved_at->format('M d, Y') }}</span>
                                        <br>
                                        <small class="text-muted">{{ $student->admin_approved_at->format('H:i') }} ({{ $student->admin_approved_at->diffForHumans() }})</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="fas fa-user-shield me-1"></i>Admin
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <button type="button" class="btn btn-outline-primary" title="View Details" onclick="viewStudentDetails({{ $student->id }})">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger" title="Revoke Access" onclick="revokeStudent({{ $student->id }}, '{{ $student->name }}')">
                                            <i class="fas fa-user-times"></i>
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
                <i class="fas fa-user-check fa-3x text-muted mb-3"></i>
                <h5>No Approved Students</h5>
                <p class="text-muted">No students have been approved yet. Check the pending students section.</p>
                <a href="{{ route('admin.pending-students') }}" class="btn btn-warning">
                    <i class="fas fa-clock me-2"></i>View Pending Students
                </a>
            </div>
        @endif
    </div>
    @if (method_exists($approvedStudents, 'links') && $approvedStudents->hasPages())
        <div class="card-footer bg-white">
            <div class="d-flex justify-content-center">
                {{ $approvedStudents->links() }}
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
                    <i class="fas fa-user me-2"></i>Student Details
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

<!-- Revoke Access Form -->
<form id="revokeForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
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
    background-color: rgba(40, 167, 69, 0.05);
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
                        <h6 class="text-muted">Approval Date</h6>
                        <p>${data.admin_approved_at || 'Not approved'}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-muted">Status</h6>
                        <span class="badge bg-success">${data.status}</span>
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

function revokeStudent(studentId, studentName) {
    if (confirm(`Are you sure you want to revoke access for ${studentName}? This will completely delete their account and free up their email for future registrations.`)) {
        const form = document.getElementById('revokeForm');
        form.action = `/admin/students/${studentId}/revoke`;
        form.submit();
    }
}
</script>
@endpush