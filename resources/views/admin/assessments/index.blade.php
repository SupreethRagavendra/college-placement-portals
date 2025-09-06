@extends('layouts.admin')

@section('title','Assessments')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="mb-0">Assessments</h1>
        <a href="{{ url('/admin/assessments/create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Create Assessment</a>
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
                        <th style="width: 200px" class="text-end">Actions</th>
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
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ url('/admin/assessments/' . ($assessment['id'] ?? '') . '/questions') }}" class="btn btn-outline-primary">Questions</a>
                                <a href="{{ url('/admin/assessments/' . ($assessment['id'] ?? '') . '/edit') }}" class="btn btn-outline-secondary">Edit</a>
                                <form action="{{ url('/admin/assessments/' . ($assessment['id'] ?? '') . '/delete') }}" method="POST" onsubmit="return confirm('Delete this assessment?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center p-4">No assessments found</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
