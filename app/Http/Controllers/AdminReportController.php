<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Question;
use App\Models\StudentResult;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminReportController extends Controller
{
    public function index(): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        // Get assessment statistics
        $assessmentStats = Assessment::withCount(['studentResults', 'questions'])
            ->with(['studentResults' => function($query) {
                $query->select('assessment_id', 'score', 'total_questions', 'time_taken');
            }])
            ->get()
            ->map(function($assessment) {
                $results = $assessment->studentResults;
                return [
                    'id' => $assessment->id,
                    'title' => $assessment->title,
                    'category' => $assessment->category,
                    'total_questions' => $assessment->questions_count,
                    'total_attempts' => $assessment->student_results_count,
                    'avg_score' => $results->avg('score') ?? 0,
                    'avg_percentage' => $results->count() > 0 
                        ? round($results->avg(function($result) {
                            return $result->total_questions > 0 ? ($result->score / $result->total_questions) * 100 : 0;
                        }), 2) : 0,
                    'avg_time' => round($results->avg('time_taken') ?? 0),
                    'highest_score' => $results->max('score') ?? 0,
                    'lowest_score' => $results->min('score') ?? 0,
                    'pass_rate' => $results->count() > 0 
                        ? round($results->filter(function($result) {
                            return $result->total_questions > 0 && (($result->score / $result->total_questions) * 100) >= 50;
                        })->count() / $results->count() * 100, 2) : 0,
                ];
            });

        // Overall statistics
        $overallStats = [
            'total_assessments' => Assessment::count(),
            'total_questions' => Question::count(),
            'total_students' => User::where('role', 'student')->count(),
            'total_attempts' => StudentResult::count(),
            'average_score' => StudentResult::selectRaw('AVG((score / total_questions) * 100) as avg')
                ->value('avg') ?? 0,
        ];

        return view('admin.reports.index', compact('assessmentStats', 'overallStats'));
    }

    public function assessmentDetails(Request $request, Assessment $assessment): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $query = StudentResult::where('assessment_id', $assessment->id)
            ->with(['student:id,name,email', 'assessment:id,title,category']);

        // Apply filters
        if ($request->filled('search')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('email', 'LIKE', '%' . $request->search . '%');
            });
        }

        if ($request->filled('grade')) {
            switch ($request->grade) {
                case 'A':
                    $query->whereRaw('(score / total_questions) * 100 >= 90');
                    break;
                case 'B':
                    $query->whereRaw('(score / total_questions) * 100 >= 80 AND (score / total_questions) * 100 < 90');
                    break;
                case 'C':
                    $query->whereRaw('(score / total_questions) * 100 >= 70 AND (score / total_questions) * 100 < 80');
                    break;
                case 'D':
                    $query->whereRaw('(score / total_questions) * 100 >= 60 AND (score / total_questions) * 100 < 70');
                    break;
                case 'F':
                    $query->whereRaw('(score / total_questions) * 100 < 60');
                    break;
            }
        }

        $results = $query->orderBy('submitted_at', 'desc')
            ->paginate(20);

        // Assessment statistics
        $stats = [
            'total_attempts' => StudentResult::where('assessment_id', $assessment->id)->count(),
            'avg_score' => StudentResult::where('assessment_id', $assessment->id)
                ->selectRaw('AVG((score / total_questions) * 100) as avg')
                ->value('avg') ?? 0,
            'pass_rate' => StudentResult::where('assessment_id', $assessment->id)
                ->whereRaw('(score / total_questions) * 100 >= 50')
                ->count() / max(StudentResult::where('assessment_id', $assessment->id)->count(), 1) * 100,
            'avg_time' => StudentResult::where('assessment_id', $assessment->id)
                ->avg('time_taken') ?? 0,
        ];

        return view('admin.reports.assessment-details', compact('assessment', 'results', 'stats'));
    }

    public function studentPerformance(Request $request): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $query = StudentResult::select('student_id')
            ->selectRaw('COUNT(*) as total_attempts')
            ->selectRaw('AVG((score / total_questions) * 100) as avg_percentage')
            ->selectRaw('MAX((score / total_questions) * 100) as best_percentage')
            ->selectRaw('SUM(time_taken) as total_time')
            ->with('student:id,name,email')
            ->groupBy('student_id');

        // Apply filters
        if ($request->filled('search')) {
            $query->whereHas('student', function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('email', 'LIKE', '%' . $request->search . '%');
            });
        }

        if ($request->filled('min_attempts')) {
            $query->having('total_attempts', '>=', $request->min_attempts);
        }

        $studentPerformance = $query->orderBy('avg_percentage', 'desc')
            ->paginate(20);

        return view('admin.reports.student-performance', compact('studentPerformance'));
    }

    public function categoryAnalysis(): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $categoryStats = DB::table('student_results')
            ->join('assessments', 'student_results.assessment_id', '=', 'assessments.id')
            ->select('assessments.category')
            ->selectRaw('COUNT(student_results.id) as total_attempts')
            ->selectRaw('AVG((score / total_questions) * 100) as avg_percentage')
            ->selectRaw('COUNT(DISTINCT student_results.student_id) as unique_students')
            ->selectRaw('COUNT(DISTINCT assessments.id) as total_assessments')
            ->groupBy('assessments.category')
            ->get();

        // Difficulty analysis
        $difficultyStats = DB::table('student_results')
            ->join('assessments', 'student_results.assessment_id', '=', 'assessments.id')
            ->join('assessment_questions', 'assessments.id', '=', 'assessment_questions.assessment_id')
            ->join('questions', 'assessment_questions.question_id', '=', 'questions.id')
            ->select('questions.difficulty')
            ->selectRaw('AVG((score / total_questions) * 100) as avg_percentage')
            ->selectRaw('COUNT(student_results.id) as total_attempts')
            ->groupBy('questions.difficulty')
            ->get();

        return view('admin.reports.category-analysis', compact('categoryStats', 'difficultyStats'));
    }

    public function exportCsv(Request $request): Response
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $type = $request->get('type', 'all'); // all, assessment, student
        $assessmentId = $request->get('assessment_id');

        $query = StudentResult::with(['student:id,name,email', 'assessment:id,title,category']);

        if ($type === 'assessment' && $assessmentId) {
            $query->where('assessment_id', $assessmentId);
        }

        $results = $query->orderBy('submitted_at', 'desc')->get();

        $csvData = [];
        $csvData[] = [
            'Student Name',
            'Student Email', 
            'Assessment Title',
            'Category',
            'Score',
            'Total Questions',
            'Percentage',
            'Grade',
            'Time Taken (minutes)',
            'Submitted At'
        ];

        foreach ($results as $result) {
            $percentage = $result->score_percentage;
            $grade = $result->grade;
            $timeInMinutes = round($result->time_taken / 60, 2);

            $csvData[] = [
                $result->student->name,
                $result->student->email,
                $result->assessment->title,
                $result->assessment->category,
                $result->score,
                $result->total_questions,
                $percentage . '%',
                $grade,
                $timeInMinutes,
                $result->submitted_at->format('Y-m-d H:i:s')
            ];
        }

        $filename = 'assessment_results_' . date('Y-m-d_H-i-s') . '.csv';
        
        $callback = function() use ($csvData) {
            $file = fopen('php://output', 'w');
            
            foreach ($csvData as $row) {
                fputcsv($file, $row);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function questionAnalysis(Assessment $assessment): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $questions = $assessment->questions()->get();
        $questionStats = [];

        foreach ($questions as $question) {
            // Count correct and incorrect answers for this question
            $results = StudentResult::where('assessment_id', $assessment->id)
                ->whereNotNull('answers')
                ->get();

            $totalAttempts = 0;
            $correctAttempts = 0;
            $optionCounts = [0, 0, 0, 0]; // For options A, B, C, D

            foreach ($results as $result) {
                $answers = $result->answers;
                if (isset($answers[$question->id])) {
                    $totalAttempts++;
                    $userAnswer = $answers[$question->id];
                    
                    // Count option selection
                    if ($userAnswer >= 0 && $userAnswer < 4) {
                        $optionCounts[$userAnswer]++;
                    }
                    
                    // Check if correct
                    if ($question->isCorrectAnswer($userAnswer)) {
                        $correctAttempts++;
                    }
                }
            }

            $questionStats[] = [
                'question' => $question,
                'total_attempts' => $totalAttempts,
                'correct_attempts' => $correctAttempts,
                'accuracy_rate' => $totalAttempts > 0 ? round(($correctAttempts / $totalAttempts) * 100, 2) : 0,
                'option_counts' => $optionCounts,
                'option_percentages' => $totalAttempts > 0 
                    ? array_map(function($count) use ($totalAttempts) {
                        return round(($count / $totalAttempts) * 100, 2);
                    }, $optionCounts)
                    : [0, 0, 0, 0]
            ];
        }

        return view('admin.reports.question-analysis', compact('assessment', 'questionStats'));
    }
}