@extends('layouts.admin')

@section('title','Assessment Questions')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <div>
            <h1 class="mb-0">{{ $assessment['name'] ?? 'Assessment' }} - Questions</h1>
            <p class="text-muted mb-0">{{ $assessment['category'] ?? '' }} • {{ $assessment['time_limit'] ?? 0 }} minutes</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ url('/admin/assessments/' . ($assessment['id'] ?? '') . '/add-question') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Question</a>
            <a class="btn btn-outline-secondary" href="{{ url('/admin/assessments') }}">Back to Assessments</a>
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
                        <th>Question</th>
                        <th style="width: 100px">Correct</th>
                        <th style="width: 120px">Difficulty</th>
                        <th style="width: 160px" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($questions as $question)
                    <tr>
                        <td>{{ $question['id'] ?? '' }}</td>
                        <td>{{ Str::limit($question['question'] ?? '', 120) }}</td>
                        <td><span class="badge bg-dark">{{ $question['correct_option'] ?? '' }}</span></td>
                        <td>
                            @php $diff = $question['difficulty'] ?? ''; @endphp
                            <span class="badge bg-{{ $diff==='Easy'?'success':($diff==='Medium'?'warning text-dark':($diff==='Hard'?'danger':'secondary')) }}">{{ $diff ?: '—' }}</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ url('/admin/assessments/' . ($assessment['id'] ?? '') . '/questions/' . ($question['id'] ?? '') . '/edit') }}" class="btn btn-outline-primary">Edit</a>
                                <form action="{{ url('/admin/assessments/' . ($assessment['id'] ?? '') . '/questions/' . ($question['id'] ?? '') . '/delete') }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this question?');" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center p-4">No questions found for this assessment</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
