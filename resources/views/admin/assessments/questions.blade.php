@extends('layouts.admin')

@section('title', 'Manage Questions')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1>Manage Questions</h1>
        <p class="text-muted mb-0">{{ $assessment->title }}</p>
    </div>
    <div>
        <a href="{{ route('admin.assessments.add-question', $assessment) }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Question
        </a>
        @if($questions->count() > 0)
        <form action="{{ route('admin.assessments.remove-all-questions', $assessment) }}" method="POST" class="d-inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to remove ALL questions from this assessment? This action cannot be undone.')">
                <i class="fas fa-trash-alt"></i> Remove All Questions
            </button>
        </form>
        @endif
        <a href="{{ route('admin.assessments.show', $assessment) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
</div>

@if(session('status'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('status') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Statistics -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h5>Total Questions</h5>
                <h2>{{ $allQuestions->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body">
                <h5>Easy Questions</h5>
                <h2>{{ $allQuestions->where('difficulty_level', 'easy')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <h5>Medium Questions</h5>
                <h2>{{ $allQuestions->where('difficulty_level', 'medium')->count() }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body">
                <h5>Hard Questions</h5>
                <h2>{{ $allQuestions->where('difficulty_level', 'hard')->count() }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Questions List -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-list"></i> Questions List</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th width="5%">#</th>
                    <th width="45%">Question</th>
                    <th width="15%">Difficulty</th>
                    <th width="10%">Marks</th>
                    <th width="10%">Active</th>
                    <th width="15%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($questions as $index => $question)
                <tr>
                    <td>{{ $questions->firstItem() + $index }}</td>
                    <td>
                        <strong>{{ Str::limit($question->question_text, 100) }}</strong>
                        @if(is_array($question->options) && count($question->options) > 0)
                        <br><small class="text-muted">
                            Options: {{ count($question->options) }} | 
                            Correct: Option {{ chr(65 + $question->correct_option) }}
                        </small>
                        @endif
                    </td>
                    <td>
                        @if($question->difficulty_level == 'easy')
                            <span class="badge bg-success">Easy</span>
                        @elseif($question->difficulty_level == 'medium')
                            <span class="badge bg-warning">Medium</span>
                        @else
                            <span class="badge bg-danger">Hard</span>
                        @endif
                    </td>
                    <td>{{ $question->marks ?? 1 }}</td>
                    <td>
                        @if($question->is_active)
                            <span class="badge bg-success">Yes</span>
                        @else
                            <span class="badge bg-secondary">No</span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <button type="button" class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#viewModal{{ $question->id }}">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="{{ route('admin.assessments.edit-question', [$assessment, $question]) }}" class="btn btn-sm btn-outline-warning">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.assessments.delete-question', [$assessment, $question]) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Remove this question from assessment?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>

                <!-- View Modal -->
                <div class="modal fade" id="viewModal{{ $question->id }}" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Question Details</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <h6><strong>Question:</strong></h6>
                                <p>{{ $question->question_text }}</p>
                                
                                @if(is_array($question->options) && count($question->options) > 0)
                                <h6><strong>Options:</strong></h6>
                                <ul class="list-group mb-3">
                                    @foreach($question->options as $index => $option)
                                    <li class="list-group-item {{ $index == $question->correct_option ? 'list-group-item-success' : '' }}">
                                        <strong>{{ chr(65 + $index) }}:</strong> {{ $option }}
                                        @if($index == $question->correct_option)
                                            <span class="badge bg-success float-end">Correct Answer</span>
                                        @endif
                                    </li>
                                    @endforeach
                                </ul>
                                @endif

                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Difficulty:</strong> 
                                        <span class="badge bg-{{ $question->difficulty_level == 'easy' ? 'success' : ($question->difficulty_level == 'hard' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($question->difficulty_level) }}
                                        </span>
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Marks:</strong> {{ $question->marks ?? 1 }}
                                    </div>
                                    <div class="col-md-4">
                                        <strong>Status:</strong> 
                                        <span class="badge bg-{{ $question->is_active ? 'success' : 'secondary' }}">
                                            {{ $question->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <a href="{{ route('admin.assessments.edit-question', [$assessment, $question]) }}" class="btn btn-primary">Edit Question</a>
                            </div>
                        </div>
                    </div>
                </div>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                        <p class="text-muted mb-3">No questions added yet</p>
                        <a href="{{ route('admin.assessments.add-question', $assessment) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Your First Question
                        </a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($questions->hasPages())
    <div class="card-footer">
        {{ $questions->links() }}
    </div>
    @endif
</div>

@endsection

@section('styles')
<style>
.card {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 1.5rem;
}

.btn-group .btn {
    padding: 0.25rem 0.5rem;
}

.table td {
    vertical-align: middle;
}

.modal-body ul {
    padding-left: 0;
}

.list-group-item-success {
    background-color: #d4edda;
    border-color: #c3e6cb;
}
</style>
@endsection