<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Question;
use App\Models\StudentResult;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StudentAssessmentController extends Controller
{
    public function index(): View
    {
        if (!Auth::check() || !Auth::user()->isStudent()) {
            abort(403);
        }

        $assessments = Assessment::active()
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->get();

        // Get user's results for each assessment
        $userResults = StudentResult::where('student_id', Auth::id())
            ->get()
            ->keyBy('assessment_id');

        return view('student.assessments.index', compact('assessments', 'userResults'));
    }

    public function show(Assessment $assessment): View
    {
        if (!Auth::check() || !Auth::user()->isStudent()) {
            abort(403);
        }

        if (!$assessment->is_active) {
            abort(404, 'Assessment not found or not active');
        }

        // Check if user has already taken this assessment
        $existingResult = StudentResult::where('student_id', Auth::id())
            ->where('assessment_id', $assessment->id)
            ->first();

        if ($existingResult) {
            return redirect()->route('student.assessment.result', $assessment)
                ->with('info', 'You have already taken this assessment.');
        }

        // Get questions for this assessment
        $questions = $assessment->questions()->active()->get();
        
        if ($questions->isEmpty()) {
            return redirect()->route('student.assessments.index')
                ->with('error', 'This assessment has no questions.');
        }

        // Shuffle questions for randomization
        $questions = $questions->shuffle();
        
        return view('student.assessments.show', compact('assessment', 'questions'));
    }

    public function start(Assessment $assessment): View
    {
        if (!Auth::check() || !Auth::user()->isStudent()) {
            abort(403);
        }

        if (!$assessment->is_active) {
            abort(404, 'Assessment not found or not active');
        }

        // Check if user has already taken this assessment
        $existingResult = StudentResult::where('student_id', Auth::id())
            ->where('assessment_id', $assessment->id)
            ->first();

        if ($existingResult) {
            return redirect()->route('student.assessment.result', $assessment)
                ->with('info', 'You have already taken this assessment.');
        }

        // Get questions for this assessment
        $questions = $assessment->questions()->active()->get();
        
        if ($questions->isEmpty()) {
            return redirect()->route('student.assessments.index')
                ->with('error', 'This assessment has no questions.');
        }

        // Shuffle questions for randomization
        $questions = $questions->shuffle();
        
        return view('student.assessments.take', compact('assessment', 'questions'));
    }

    public function submit(Request $request, Assessment $assessment): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isStudent()) {
            abort(403);
        }

        if (!$assessment->is_active) {
            abort(404, 'Assessment not found or not active');
        }

        // Check if user has already taken this assessment
        $existingResult = StudentResult::where('student_id', Auth::id())
            ->where('assessment_id', $assessment->id)
            ->first();

        if ($existingResult) {
            return redirect()->route('student.assessment.result', $assessment)
                ->with('info', 'You have already taken this assessment.');
        }

        $validated = $request->validate([
            'answers' => 'required|array',
            'time_taken' => 'required|integer|min:1',
        ]);

        // Get all questions for this assessment
        $questions = $assessment->questions()->active()->get()->keyBy('id');
        $totalQuestions = $questions->count();
        $correctAnswers = 0;
        $userAnswers = [];

        // Calculate score
        foreach ($validated['answers'] as $questionId => $answerIndex) {
            $question = $questions->get($questionId);
            if ($question) {
                $userAnswers[$questionId] = (int)$answerIndex;
                if ($question->isCorrectAnswer((int)$answerIndex)) {
                    $correctAnswers++;
                }
            }
        }

        // Store result
        $result = StudentResult::create([
            'student_id' => Auth::id(),
            'assessment_id' => $assessment->id,
            'score' => $correctAnswers,
            'total_questions' => $totalQuestions,
            'time_taken' => $validated['time_taken'],
            'answers' => $userAnswers,
            'submitted_at' => now(),
        ]);

        return redirect()->route('student.assessment.result', $assessment)
            ->with('status', 'Assessment submitted successfully!');
    }

    public function result(Assessment $assessment): View
    {
        if (!Auth::check() || !Auth::user()->isStudent()) {
            abort(403);
        }

        $result = StudentResult::where('student_id', Auth::id())
            ->where('assessment_id', $assessment->id)
            ->with(['assessment', 'student'])
            ->first();

        if (!$result) {
            return redirect()->route('student.assessments.index')
                ->with('error', 'No result found for this assessment.');
        }

        // Get questions with user answers for detailed review
        $questions = $assessment->questions()->get()->keyBy('id');
        $detailedResults = [];

        foreach ($result->answers as $questionId => $userAnswer) {
            $question = $questions->get($questionId);
            if ($question) {
                $detailedResults[] = [
                    'question' => $question,
                    'user_answer' => $userAnswer,
                    'is_correct' => $question->isCorrectAnswer($userAnswer),
                    'correct_answer' => $question->correct_option,
                ];
            }
        }

        return view('student.assessments.result', compact('result', 'assessment', 'detailedResults'));
    }

    public function history(): View
    {
        if (!Auth::check() || !Auth::user()->isStudent()) {
            abort(403);
        }

        $results = StudentResult::where('student_id', Auth::id())
            ->with('assessment')
            ->orderBy('submitted_at', 'desc')
            ->paginate(10);

        // Calculate overall statistics
        $stats = [
            'total_assessments' => $results->total(),
            'average_score' => StudentResult::where('student_id', Auth::id())
                ->selectRaw('AVG((score / total_questions) * 100) as avg_percentage')
                ->value('avg_percentage') ?? 0,
            'highest_score' => StudentResult::where('student_id', Auth::id())
                ->selectRaw('MAX((score / total_questions) * 100) as max_percentage')
                ->value('max_percentage') ?? 0,
            'total_time_spent' => StudentResult::where('student_id', Auth::id())
                ->sum('time_taken'),
        ];

        return view('student.assessments.history', compact('results', 'stats'));
    }

    public function analytics(): View
    {
        if (!Auth::check() || !Auth::user()->isStudent()) {
            abort(403);
        }

        $studentId = Auth::id();

        // Get performance by category
        $categoryPerformance = DB::table('student_results')
            ->join('assessments', 'student_results.assessment_id', '=', 'assessments.id')
            ->where('student_results.student_id', $studentId)
            ->select('assessments.category')
            ->selectRaw('AVG((score / total_questions) * 100) as avg_percentage')
            ->selectRaw('COUNT(*) as attempts')
            ->groupBy('assessments.category')
            ->get();

        // Get monthly performance trend
        $monthlyPerformance = StudentResult::where('student_id', $studentId)
            ->selectRaw('YEAR(submitted_at) as year, MONTH(submitted_at) as month')
            ->selectRaw('AVG((score / total_questions) * 100) as avg_percentage')
            ->selectRaw('COUNT(*) as attempts')
            ->groupBy('year', 'month')
            ->orderBy('year', 'desc')
            ->orderBy('month', 'desc')
            ->limit(6)
            ->get();

        // Get difficulty-wise performance
        $difficultyPerformance = DB::table('student_results')
            ->join('assessments', 'student_results.assessment_id', '=', 'assessments.id')
            ->join('assessment_questions', 'assessments.id', '=', 'assessment_questions.assessment_id')
            ->join('questions', 'assessment_questions.question_id', '=', 'questions.id')
            ->where('student_results.student_id', $studentId)
            ->select('questions.difficulty')
            ->selectRaw('AVG((score / total_questions) * 100) as avg_percentage')
            ->groupBy('questions.difficulty')
            ->get();

        return view('student.assessments.analytics', compact(
            'categoryPerformance', 
            'monthlyPerformance', 
            'difficultyPerformance'
        ));
    }
}