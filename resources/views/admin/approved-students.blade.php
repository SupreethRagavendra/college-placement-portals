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
                            <th>Approved By</th>
                            <th width="120" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($approvedStudents as $student)
                            <tr data-student-id="{{ $student->id }}">
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-success text-white rounded-circle d-flex align-items-center justify-content-center me-3">
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-semibold">{{ $student->name }}</div>
                                            <small class="text-muted">ID: {{ $student->id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $student->email }}</td>
                                <td>
                                    <small class="text-muted">
                                        {{ $student->admin_approved_at ? \Carbon\Carbon::parse($student->admin_approved_at)->format('M d, Y h:i A') : 'N/A' }}
                                    </small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                onclick="viewStudentDetails({{ $student->id }})"
                                                title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                onclick="revokeStudent({{ $student->id }}, '{{ addslashes($student->name) }}')"
                                                title="Revoke Access">
                                            <i class="fas fa-user-times"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            @if($approvedStudents->hasPages())
                <div class="card-footer bg-white">
                    <div class="d-flex justify-content-center">
                        {{ $approvedStudents->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            @endif
        @else
            <div class="text-center py-5">
                <i class="fas fa-user-check fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No Approved Students</h5>
                <p class="text-muted">There are no approved students at the moment.</p>
                <a href="{{ route('admin.pending-students') }}" class="btn btn-primary">
                    <i class="fas fa-clock me-2"></i>View Pending Students
                </a>
            </div>
        @endif
    </div>
</div>

<!-- Student Details Modal -->
<div class="modal fade" id="studentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Student Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="studentDetailsContent">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function viewStudentDetails(studentId) {
    const modal = new bootstrap.Modal(document.getElementById('studentDetailsModal'));
    modal.show();
    
    // Reset content to loading state
    document.getElementById('studentDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    `;
    
    fetch(`/admin/students/${studentId}/details`)
        .then(response => response.json())
        .then(data => {
            let assessmentsHtml = '';
            if (data.assessments && data.assessments.length > 0) {
                assessmentsHtml = `
                    <h6 class="mt-3">Assessment History</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Assessment</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.assessments.map(a => `
                                    <tr>
                                        <td>${a.title}</td>
                                        <td>${a.score || 'N/A'}</td>
                                        <td>
                                            <span class="badge bg-${a.status === 'completed' ? 'success' : 'warning'}">
                                                ${a.status}
                                            </span>
                                        </td>
                                        <td>${new Date(a.date).toLocaleDateString()}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            } else {
                assessmentsHtml = '<p class="text-muted mt-3">No assessment history available.</p>';
            }
            
            document.getElementById('studentDetailsContent').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6>Personal Information</h6>
                        <table class="table table-sm">
                            <tr><th width="40%">Name:</th><td>${data.name}</td></tr>
                            <tr><th>Email:</th><td>${data.email}</td></tr>
                            <tr><th>ID:</th><td>${data.id}</td></tr>
                            <tr><th>Status:</th><td><span class="badge bg-success">Approved</span></td></tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <h6>Account Information</h6>
                        <table class="table table-sm">
                            <tr><th width="40%">Registered:</th><td>${new Date(data.created_at).toLocaleDateString()}</td></tr>
                            <tr><th>Verified:</th><td>${data.verified_at ? new Date(data.verified_at).toLocaleDateString() : 'Not verified'}</td></tr>
                            <tr><th>Approved:</th><td>${data.admin_approved_at ? new Date(data.admin_approved_at).toLocaleDateString() : 'N/A'}</td></tr>
                        </table>
                    </div>
                </div>
                ${assessmentsHtml}
            `;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('studentDetailsContent').innerHTML = `
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Failed to load student details. Please try again.
                </div>
            `;
        });
}

function revokeStudent(studentId, studentName) {
    if (confirm(`Are you sure you want to revoke access for ${studentName}? This will completely delete their account and free up their email for future registrations.`)) {
        // Show loading state
        const row = document.querySelector(`tr[data-student-id="${studentId}"]`);
        if (row) {
            row.style.opacity = '0.5';
            row.style.pointerEvents = 'none';
        }
        
        fetch(`/admin/students/${studentId}/revoke`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => {
            if (response.ok) {
                // Animate row removal
                if (row) {
                    row.style.transition = 'all 0.3s ease-out';
                    row.style.transform = 'translateX(-100%)';
                    row.style.opacity = '0';
                    setTimeout(() => {
                        row.remove();
                        // Update count
                        updateApprovedCount();
                        // Check if table is empty
                        const tbody = document.querySelector('tbody');
                        if (tbody && tbody.children.length === 0) {
                            // Reload to show empty state
                            location.reload();
                        }
                    }, 300);
                }
                // Show success message
                showAlert('success', `Access for ${studentName} has been revoked successfully.`);
            } else {
                throw new Error('Failed to revoke access');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Restore row state
            if (row) {
                row.style.opacity = '1';
                row.style.pointerEvents = 'auto';
            }
            showAlert('danger', 'An error occurred while revoking access. Please try again.');
        });
    }
}

function updateApprovedCount() {
    const rows = document.querySelectorAll('tbody tr[data-student-id]');
    const count = rows.length;
    const badge = document.querySelector('.badge.bg-success.fs-6');
    if (badge) {
        badge.textContent = `${count} approved student${count !== 1 ? 's' : ''}`;
    }
}

function showAlert(type, message) {
    const alertHtml = `
        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    // Remove existing alerts
    const existingAlerts = document.querySelectorAll('.alert');
    existingAlerts.forEach(alert => alert.remove());
    
    // Add new alert
    const container = document.querySelector('.card').parentElement;
    container.insertAdjacentHTML('afterbegin', alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        const alert = document.querySelector('.alert');
        if (alert) {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 150);
        }
    }, 5000);
}
</script>
@endpush

@endsection