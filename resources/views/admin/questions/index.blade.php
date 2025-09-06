@extends('layouts.admin')

@section('title','Questions')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
        <h1 class="mb-0">Questions</h1>
        <a href="{{ url('/admin/questions/create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add Question</a>
    </div>

    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="card mb-3">
        <div class="card-body">
            <form class="row g-2 align-items-end" method="GET" action="{{ url('/admin/questions') }}">
                <div class="col-sm-3">
                    <label class="form-label">Category</label>
                    <select class="form-select" name="category">
                        <option value="">All</option>
                        <option value="Aptitude" {{ ($category ?? '')==='Aptitude' ? 'selected' : '' }}>Aptitude</option>
                        <option value="Technical" {{ ($category ?? '')==='Technical' ? 'selected' : '' }}>Technical</option>
                    </select>
                </div>
                <div class="col-sm-6">
                    <label class="form-label">Search</label>
                    <input class="form-control" name="q" value="{{ $q ?? '' }}" placeholder="Search question text" />
                </div>
                <div class="col-sm-3 d-flex gap-2">
                    <button class="btn btn-outline-primary w-100" type="submit">Filter</button>
                    <a class="btn btn-outline-secondary w-100" href="{{ url('/admin/questions') }}">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <form class="row g-2 align-items-end" method="POST" action="{{ url('/admin/questions/settings') }}">
                @csrf
                <div class="col-sm-3">
                    <label class="form-label">Aptitude Timer (minutes)</label>
                    <input class="form-control" type="number" name="aptitude_minutes" min="1" max="300" value="{{ $settings['Aptitude'] ?? 30 }}" required />
                </div>
                <div class="col-sm-3">
                    <label class="form-label">Technical Timer (minutes)</label>
                    <input class="form-control" type="number" name="technical_minutes" min="1" max="300" value="{{ $settings['Technical'] ?? 30 }}" required />
                </div>
                <div class="col-sm-3">
                    <button class="btn btn-outline-primary mt-4" type="submit">Save Settings</button>
                </div>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 70px">ID</th>
                        <th style="width: 120px">Category</th>
                        <th>Question</th>
                        <th style="width: 100px">Correct</th>
                        <th style="width: 120px">Difficulty</th>
                        <th style="width: 160px" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($questions as $qRow)
                    <tr>
                        <td>{{ $qRow['id'] ?? '' }}</td>
                        <td><span class="badge bg-{{ ($qRow['category'] ?? '')==='Aptitude' ? 'primary' : 'success' }}">{{ $qRow['category'] ?? '' }}</span></td>
                        <td>{{ \Illuminate\Support\Str::limit($qRow['question_text'] ?? '', 120) }}</td>
                        <td><span class="badge bg-dark">{{ $qRow['correct_answer'] ?? '' }}</span></td>
                        <td>
                            @php $diff = $qRow['difficulty'] ?? ''; @endphp
                            <span class="badge bg-{{ $diff==='Easy'?'success':($diff==='Medium'?'warning text-dark':($diff==='Hard'?'danger':'secondary')) }}">{{ $diff ?: '—' }}</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="{{ url('/admin/questions/' . ($qRow['id'] ?? '') . '/edit') }}" class="btn btn-outline-secondary">Edit</a>
                                <form action="{{ url('/admin/questions/' . ($qRow['id'] ?? '') . '/delete') }}" method="POST" onsubmit="return confirm('Delete this question?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center p-4">No questions found</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div>Page {{ $page }}</div>
            <div class="d-flex gap-2">
                @php $query = http_build_query(array_filter(['category'=>$category,'q'=>$q])); @endphp
                @if ($hasPrev)
                    <a class="btn btn-outline-secondary btn-sm" href="{{ url('/admin/questions?page=' . ($page-1) . ($query?('&'.$query):'')) }}">Previous</a>
                @endif
                @if ($hasNext)
                    <a class="btn btn-outline-secondary btn-sm" href="{{ url('/admin/questions?page=' . ($page+1) . ($query?('&'.$query):'')) }}">Next</a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


