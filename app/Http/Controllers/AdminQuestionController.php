<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Assessment;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AdminQuestionController extends Controller
{
    public function index(Request $request): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $query = Question::query();

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by difficulty
        if ($request->filled('difficulty')) {
            $query->where('difficulty', $request->difficulty);
        }

        // Filter by active status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search by question text
        if ($request->filled('search')) {
            $query->where('question_text', 'LIKE', '%' . $request->search . '%');
        }

        $questions = $query->withCount('assessments')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.questions.index', compact('questions'));
    }

    public function create(): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        return view('admin.questions.create');
    }

    public function store(Request $request): RedirectResponse
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
            'category' => 'required|in:Aptitude,Technical',
            'difficulty' => 'required|in:Easy,Medium,Hard',
            'time_per_question' => 'nullable|integer|min:10|max:300',
            'is_active' => 'boolean',
        ]);

        Question::create([
            'question_text' => $validated['question_text'],
            'options' => [
                $validated['option_a'],
                $validated['option_b'],
                $validated['option_c'],
                $validated['option_d'],
            ],
            'correct_option' => (int)$validated['correct_answer'],
            'category' => $validated['category'],
            'difficulty' => $validated['difficulty'],
            'time_per_question' => $validated['time_per_question'] ?? 60,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.questions.index')
            ->with('status', 'Question created successfully');
    }

    public function show(Question $question): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        $question->load('assessments');
        
        return view('admin.questions.show', compact('question'));
    }

    public function edit(Question $question): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        return view('admin.questions.edit', compact('question'));
    }

    public function update(Request $request, Question $question): RedirectResponse
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
            'category' => 'required|in:Aptitude,Technical',
            'difficulty' => 'required|in:Easy,Medium,Hard',
            'time_per_question' => 'nullable|integer|min:10|max:300',
            'is_active' => 'boolean',
        ]);

        $question->update([
            'question_text' => $validated['question_text'],
            'options' => [
                $validated['option_a'],
                $validated['option_b'],
                $validated['option_c'],
                $validated['option_d'],
            ],
            'correct_option' => (int)$validated['correct_answer'],
            'category' => $validated['category'],
            'difficulty' => $validated['difficulty'],
            'time_per_question' => $validated['time_per_question'] ?? 60,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.questions.index')
            ->with('status', 'Question updated successfully');
    }

    public function destroy(Question $question): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        // Check if question is used in any assessments
        if ($question->assessments()->exists()) {
            return back()->withErrors(['error' => 'Cannot delete question as it is used in assessments. Remove it from assessments first.']);
        }
        
        $question->delete();
        
        return redirect()->route('admin.questions.index')
            ->with('status', 'Question deleted successfully');
    }

    public function bulk(Request $request): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'action' => 'required|in:activate,deactivate,delete',
            'question_ids' => 'required|array',
            'question_ids.*' => 'exists:questions,id'
        ]);

        $questions = Question::whereIn('id', $validated['question_ids']);

        switch ($validated['action']) {
            case 'activate':
                $questions->update(['is_active' => true]);
                $message = 'Questions activated successfully';
                break;
            case 'deactivate':
                $questions->update(['is_active' => false]);
                $message = 'Questions deactivated successfully';
                break;
            case 'delete':
                // Check if any questions are used in assessments
                $usedQuestions = $questions->whereHas('assessments')->count();
                if ($usedQuestions > 0) {
                    return back()->withErrors(['error' => 'Some questions are used in assessments and cannot be deleted.']);
                }
                $questions->delete();
                $message = 'Questions deleted successfully';
                break;
        }

        return back()->with('status', $message);
    }

    public function import(): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }
        
        return view('admin.questions.import');
    }

    public function processImport(Request $request): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
            'category' => 'required|in:Aptitude,Technical',
            'difficulty' => 'required|in:Easy,Medium,Hard',
        ]);

        $file = $request->file('csv_file');
        $csvData = array_map('str_getcsv', file($file->getRealPath()));
        $header = array_shift($csvData);

        $imported = 0;
        $errors = [];

        foreach ($csvData as $index => $row) {
            if (count($row) < 6) {
                $errors[] = "Row " . ($index + 2) . ": Invalid format";
                continue;
            }

            try {
                Question::create([
                    'question_text' => $row[0],
                    'options' => [
                        $row[1], // Option A
                        $row[2], // Option B
                        $row[3], // Option C
                        $row[4], // Option D
                    ],
                    'correct_option' => (int)$row[5], // 0-3 index
                    'category' => $validated['category'],
                    'difficulty' => $validated['difficulty'],
                    'time_per_question' => 60,
                    'is_active' => true,
                ]);
                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        $message = "Imported {$imported} questions successfully.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', array_slice($errors, 0, 5));
        }

        return redirect()->route('admin.questions.index')
            ->with('status', $message);
    }
}