@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Pending Students</h1>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Registered At</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $student)
                            <tr>
                                <td>{{ $student->name }}</td>
                                <td>{{ $student->email }}</td>
                                <td>{{ $student->created_at->format('M d, Y H:i') }}</td>
                                <td class="text-end">
                                    <form action="{{ url('/admin/students/' . $student->id . '/approve') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-success">Approve</button>
                                    </form>
                                    <form action="{{ url('/admin/students/' . $student->id . '/reject') }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure to reject? This will delete the student.');">
                                        @csrf
                                        <button class="btn btn-sm btn-danger">Reject</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center p-4">No pending students</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if (method_exists($students, 'links'))
        <div class="card-footer">
            {{ $students->links() }}
        </div>
        @endif
    </div>
</div>
@endsection


