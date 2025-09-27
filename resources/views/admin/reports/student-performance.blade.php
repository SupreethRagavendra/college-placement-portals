@extends('layouts.admin')

@section('title', 'Student Progress')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="mb-0">Student Progress</h1>
            <p class="text-muted mb-0">Track individual student performance and progress</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.reports.export', ['type' => 'student']) }}" class="btn btn-outline-primary">
                <i class="fas fa-download me-2"></i>Export CSV
            </a>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Reports
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Search Students</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           value="{{ request('search') }}" placeholder="Name or email...">
                </div>
                <div class="col-md-3">
                    <label for="min_attempts" class="form-label">Minimum Attempts</label>
                    <input type="number" class="form-control" id="min_attempts" name="min_attempts" 
                           value="{{ request('min_attempts') }}" min="1" placeholder="e.g., 3">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.reports.student-performance') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Student Performance Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-user-graduate me-2"></i>Student Performance Overview
            </h5>
            <span class="badge bg-primary">{{ $studentPerformance->total() }} Students</span>
        </div>
        <div class="card-body p-0">
            @if($studentPerformance->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px">#</th>
                                <th>Student</th>
                                <th style="width: 120px">Total Attempts</th>
                                <th style="width: 120px">Average Score</th>
                                <th style="width: 120px">Best Score</th>
                                <th style="width: 120px">Total Time</th>
                                <th style="width: 100px">Performance</th>
                                <th style="width: 120px" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($studentPerformance as $index => $performance)
                                @php
                                    $student = $performance->student;
                                    $avgPercentage = round($performance->avg_percentage, 1);
                                    $bestPercentage = round($performance->best_percentage, 1);
                                    $totalTimeHours = round($performance->total_time / 3600, 1);
                                    
                                    // Performance grade
                                    $grade = 'F';
                                    $gradeClass = 'danger';
                                    if ($avgPercentage >= 90) {
                                        $grade = 'A+';
                                        $gradeClass = 'success';
                                    } elseif ($avgPercentage >= 80) {
                                        $grade = 'A';
                                        $gradeClass = 'success';
                                    } elseif ($avgPercentage >= 70) {
                                        $grade = 'B';
                                        $gradeClass = 'info';
                                    } elseif ($avgPercentage >= 60) {
                                        $grade = 'C';
                                        $gradeClass = 'warning';
                                    } elseif ($avgPercentage >= 50) {
                                        $grade = 'D';
                                        $gradeClass = 'warning';
                                    }
                                @endphp
                                <tr>
                                    <td>{{ $studentPerformance->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar me-3">
                                                {{ strtoupper(substr($student->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <h6 class="mb-0">{{ $student->name }}</h6>
                                                <small class="text-muted">{{ $student->email }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary fs-6">{{ $performance->total_attempts }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="progress me-2" style="width: 60px; height: 8px;">
                                                <div class="progress-bar bg-{{ $gradeClass }}" 
                                                     style="width: {{ min($avgPercentage, 100) }}%"></div>
                                            </div>
                                            <span class="fw-bold">{{ $avgPercentage }}%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-success">{{ $bestPercentage }}%</span>
                                    </td>
                                    <td>
                                        <span class="text-muted">{{ $totalTimeHours }}h</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $gradeClass }} fs-6">{{ $grade }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-outline-primary" 
                                                    onclick="viewStudentDetails({{ $student->id }}, '{{ $student->name }}')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-outline-info" 
                                                    onclick="viewStudentHistory({{ $student->id }}, '{{ $student->name }}')">
                                                <i class="fas fa-history"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="card-footer">
                    {{ $studentPerformance->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-user-graduate fa-4x text-muted mb-3"></i>
                    <h5>No Student Performance Data</h5>
                    <p class="text-muted">No students have completed assessments yet.</p>
                    <a href="{{ route('admin.assessments.index') }}" class="btn btn-primary">
                        <i class="fas fa-clipboard-list me-2"></i>Manage Assessments
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Performance Statistics -->
    @if($studentPerformance->count() > 0)
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                        <h4>{{ $studentPerformance->total() }}</h4>
                        <p class="text-muted mb-0">Total Students</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-chart-line fa-2x text-success mb-2"></i>
                        <h4>{{ round($studentPerformance->avg('avg_percentage'), 1) }}%</h4>
                        <p class="text-muted mb-0">Average Performance</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-trophy fa-2x text-warning mb-2"></i>
                        <h4>{{ round($studentPerformance->max('best_percentage'), 1) }}%</h4>
                        <p class="text-muted mb-0">Best Performance</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-body">
                        <i class="fas fa-clock fa-2x text-info mb-2"></i>
                        <h4>{{ round($studentPerformance->sum('total_time') / 3600, 1) }}h</h4>
                        <p class="text-muted mb-0">Total Study Time</p>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<!-- Student Details Modal -->
<div class="modal fade" id="studentDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Student Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="studentDetailsContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #6f7bf7 0%, #7a56d0 100%);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: white;
    font-size: 14px;
}

.progress {
    border-radius: 10px;
    background-color: #e9ecef;
}

.progress-bar {
    border-radius: 10px;
    transition: width 0.3s ease;
}

.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}

.card {
    border: none;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}
</style>

<script>
function viewStudentDetails(studentId, studentName) {
    // This would typically make an AJAX call to get detailed student data
    document.getElementById('studentDetailsContent').innerHTML = `
        <div class="text-center py-4">
            <i class="fas fa-user fa-3x text-primary mb-3"></i>
            <h5>${studentName}</h5>
            <p class="text-muted">Detailed performance data for this student</p>
            <p class="text-muted">Student ID: ${studentId}</p>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                This feature will show detailed assessment history, progress charts, and performance analytics for the selected student.
            </div>
        </div>
    `;
    
    new bootstrap.Modal(document.getElementById('studentDetailsModal')).show();
}

function viewStudentHistory(studentId, studentName) {
    // This would typically redirect to a detailed student history page
    window.location.href = `/admin/reports/student/${studentId}/history`;
}
</script>
@endsection
