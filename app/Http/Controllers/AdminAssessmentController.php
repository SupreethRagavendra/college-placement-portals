<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\Question;
use App\Models\StudentResult;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminAssessmentController extends Controller
{
    public function index(): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $assessments = Assessment::withCount(['questions', 'studentResults'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.assessments.index', compact('assessments'));
    }

    public function create(): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        return view('admin.assessments.create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:Aptitude,Technical',
            'total_time' => 'required|integer|min:1|max:300',
            'is_active' => 'boolean',
        ]);

        Assessment::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'category' => $validated['category'],
            'total_time' => (int)$validated['total_time'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.assessments.index')
            ->with('status', 'Assessment created successfully');
    }

    public function edit(Assessment $assessment): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        return view('admin.assessments.edit', compact('assessment'));
    }

    public function update(Request $request, Assessment $assessment): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:Aptitude,Technical',
            'total_time' => 'required|integer|min:1|max:300',
            'is_active' => 'boolean',
        ]);

        $assessment->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? '',
            'category' => $validated['category'],
            'total_time' => (int)$validated['total_time'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.assessments.index')
            ->with('status', 'Assessment updated successfully');
    }

    public function destroy(Assessment $assessment): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $assessment->delete();
        return redirect()->route('admin.assessments.index')
            ->with('status', 'Assessment deleted successfully');
    }

    public function toggleStatus(Request $request, Assessment $assessment)
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        $assessment->update([
            'is_active' => $request->is_active
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Assessment status updated successfully',
            'is_active' => $assessment->is_active
        ]);
    }

    public function questions(Assessment $assessment): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $questions = $assessment->questions()->orderBy('created_at', 'desc')->get();
        $availableQuestions = Question::whereDoesntHave('assessments', function($query) use ($assessment) {
            $query->where('assessment_id', $assessment->id);
        })->where('category', $assessment->category)
          ->where('is_active', true)
          ->get();
        
        return view('admin.assessments.questions', compact('assessment', 'questions', 'availableQuestions'));
    }

    public function addQuestion(Assessment $assessment): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        return view('admin.assessments.add-question', compact('assessment'));
    }

    public function storeQuestion(Request $request, Assessment $assessment): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'question_text' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_answer' => 'required|in:0,1,2,3',
            'difficulty' => 'nullable|in:Easy,Medium,Hard',
            'time_per_question' => 'nullable|integer|min:10|max:300',
        ]);

        // Create the question
        $question = Question::create([
            'question_text' => $validated['question_text'],
            'options' => [
                $validated['option_a'],
                $validated['option_b'],
                $validated['option_c'],
                $validated['option_d'],
            ],
            'correct_option' => (int)$validated['correct_answer'],
            'category' => $assessment->category,
            'difficulty' => $validated['difficulty'] ?? 'Medium',
            'time_per_question' => $validated['time_per_question'] ?? 60,
            'is_active' => true,
        ]);

        // Link question to assessment
        $assessment->questions()->attach($question->id);
        
        return redirect()->route('admin.assessments.questions', $assessment)
            ->with('status', 'Question added to assessment successfully');
    }

    public function assignQuestion(Request $request, Assessment $assessment): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'question_id' => 'required|exists:questions,id'
        ]);

        // Check if question is not already assigned
        if (!$assessment->questions()->where('question_id', $validated['question_id'])->exists()) {
            $assessment->questions()->attach($validated['question_id']);
            return back()->with('status', 'Question assigned to assessment successfully');
        }

        return back()->withErrors(['error' => 'Question is already assigned to this assessment']);
    }

    public function editQuestion(Assessment $assessment, Question $question): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        // Ensure question belongs to assessment
        if (!$assessment->questions()->where('question_id', $question->id)->exists()) {
            abort(404);
        }
        
        return view('admin.assessments.edit-question', compact('assessment', 'question'));
    }

    public function updateQuestion(Request $request, Assessment $assessment, Question $question): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'question_text' => 'required|string',
            'option_a' => 'required|string',
            'option_b' => 'required|string',
            'option_c' => 'required|string',
            'option_d' => 'required|string',
            'correct_answer' => 'required|in:0,1,2,3',
            'difficulty' => 'nullable|in:Easy,Medium,Hard',
            'time_per_question' => 'nullable|integer|min:10|max:300',
        ]);

        // Update the question
        $question->update([
            'question_text' => $validated['question_text'],
            'options' => [
                $validated['option_a'],
                $validated['option_b'],
                $validated['option_c'],
                $validated['option_d'],
            ],
            'correct_option' => (int)$validated['correct_answer'],
            'difficulty' => $validated['difficulty'] ?? 'Medium',
            'time_per_question' => $validated['time_per_question'] ?? 60,
        ]);
        
        return redirect()->route('admin.assessments.questions', $assessment)
            ->with('status', 'Question updated successfully');
    }

    public function deleteQuestion(Assessment $assessment, Question $question): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        // Remove question from assessment
        $assessment->questions()->detach($question->id);
        
        // Optionally delete the question entirely if not used in other assessments
        if ($question->assessments()->count() === 0) {
            $question->delete();
            $message = 'Question removed from assessment and deleted (not used elsewhere)';
        } else {
            $message = 'Question removed from assessment';
        }
        
        return redirect()->route('admin.assessments.questions', $assessment)
            ->with('status', $message);
    }

    public function show(Assessment $assessment): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $assessment->load(['questions', 'studentResults.student']);
        
        return view('admin.assessments.show', compact('assessment'));
    }
}
