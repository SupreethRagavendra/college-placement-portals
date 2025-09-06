<x-app-layout>
    <x-slot name="header">
        <h2 class="h4 mb-0">Assessment History</h2>
    </x-slot>
    <div class="container py-4">
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Assessment</th>
                            <th>Category</th>
                            <th>Score</th>
                            <th>Time Taken</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($results as $result)
                        @php $assessment = $assessmentsById[(int)($result['assessment_id'] ?? 0)] ?? null; @endphp
                        <tr>
                            <td>{{ $assessment['name'] ?? 'Unknown Assessment' }}</td>
                            <td><span class="badge bg-{{ ($assessment['category'] ?? '')==='Aptitude' ? 'primary' : 'success' }}">{{ $assessment['category'] ?? '' }}</span></td>
                            <td>{{ $result['score'] ?? 0 }}/{{ $result['total_questions'] ?? 0 }}</td>
                            <td>{{ $result['time_taken'] ?? 0 }}s</td>
                            <td>{{ \Carbon\Carbon::parse($result['submitted_at'] ?? now())->format('M d, Y H:i') }}</td>
                            <td>
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('student.assessment.result', ['id' => $result['assessment_id'] ?? 0]) }}">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center p-4">No assessment attempts yet</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
