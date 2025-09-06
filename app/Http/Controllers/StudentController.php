<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\SupabaseService;

class StudentController extends Controller
{
    private SupabaseService $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    public function dashboard()
    {
        $userId = Auth::id();
        
        // Get assessments data using the new Laravel models
        $assessments = \App\Models\Assessment::active()
            ->withCount('questions')
            ->orderBy('created_at', 'desc')
            ->limit(6)
            ->get();

        // Get student's previous attempts from results using the new system
        $userResults = \App\Models\StudentResult::where('student_id', $userId)
            ->with('assessment')
            ->orderBy('submitted_at', 'desc')
            ->get()
            ->keyBy('assessment_id');

        // Map attempts to assessments for backward compatibility
        $attemptsByAssessment = [];
        foreach ($userResults as $result) {
            $attemptsByAssessment[$result->assessment_id] = [
                'score' => $result->score,
                'total_questions' => $result->total_questions,
                'time_taken' => $result->time_taken,
                'submitted_at' => $result->submitted_at,
            ];
        }

        // Calculate statistics from results
        $totalTests = $userResults->count();
        $avgScore = 0;
        $recentTests = [];
        
        if ($totalTests > 0) {
            $totalScore = 0;
            $totalQuestions = 0;
            
            foreach ($userResults as $result) {
                $totalScore += $result->score;
                $totalQuestions += $result->total_questions;
            }
            
            $avgScore = $totalQuestions > 0 ? round(($totalScore / $totalQuestions) * 100) : 0;
            
            // Get recent tests (last 5)
            $recentAttempts = $userResults->take(5);
            foreach ($recentAttempts as $result) {
                $recentTests[] = [
                    'date' => $result->submitted_at->format('Y-m-d H:i:s'),
                    'category' => $result->assessment->category ?? 'Unknown',
                    'score' => $result->total_questions > 0 ? round(($result->score / $result->total_questions) * 100) : 0,
                    'assessment_id' => $result->assessment_id,
                    'assessment_name' => $result->assessment->name ?? 'Unknown Assessment',
                ];
            }
        }

        $rankText = '—';
        if ($avgScore > 0) {
            // Calculate rank based on results
            try {
                $allResults = $this->supabase->selectFrom('results', [], ['limit' => 1000]);
                $userAverages = [];
                foreach ($allResults as $result) {
                    $studentId = (int)($result['user_id'] ?? 0);
                    if (!isset($userAverages[$studentId])) {
                        $userAverages[$studentId] = ['total_score' => 0, 'total_questions' => 0];
                    }
                    $userAverages[$studentId]['total_score'] += (int)($result['score'] ?? 0);
                    $userAverages[$studentId]['total_questions'] += (int)($result['total'] ?? 0);
                }
                
                $userAvg = $userAverages[$userId] ?? ['total_score' => 0, 'total_questions' => 0];
                $userAvgScore = $userAvg['total_questions'] > 0 ? ($userAvg['total_score'] / $userAvg['total_questions']) * 100 : 0;
                
                $betterCount = 0;
                foreach ($userAverages as $studentId => $data) {
                    if ($studentId !== $userId && $data['total_questions'] > 0) {
                        $studentAvg = ($data['total_score'] / $data['total_questions']) * 100;
                        if ($studentAvg >= $userAvgScore) {
                            $betterCount++;
                        }
                    }
                }
                
                if ($betterCount > 0) {
                    $rankText = '#' . ($betterCount + 1);
                }
            } catch (\Throwable $e) {
                // If calculation fails, keep default
            }
        }

        // Generate trend data from recent attempts
        $trendLabels = [];
        $trendScores = [];
        if (!empty($recentTests)) {
            $trendLabels = array_map(function($test) {
                return \Carbon\Carbon::parse($test['date'])->format('M d');
            }, array_reverse($recentTests));
            
            $trendScores = array_map(function($test) {
                return $test['score'];
            }, array_reverse($recentTests));
        }

        return view('student.dashboard', compact(
            'totalTests', 'avgScore', 'rankText', 'recentTests', 'trendLabels', 'trendScores',
            'assessments', 'attemptsByAssessment'
        ));
    }

    public function categories()
    {
        // Use Supabase categories or static
        $categories = [ (object)['id' => 1, 'name' => 'Aptitude'], (object)['id' => 2, 'name' => 'Technical'] ];
        return view('student.categories', ['categories' => $categories]);
    }

    public function test($id)
    {
        $categoryId = (int) $id;
        $categoryName = $categoryId === 1 ? 'Aptitude' : 'Technical';

        $userId = Auth::id();

        // Create a test row in Supabase
        $testRow = $this->supabase->insertInto('tests', [
            'student_id' => $userId,
            'category' => $categoryName,
            'attempted_at' => now()->toIso8601String(),
            'score' => 0,
            'time_taken' => 0,
        ]);
        $testId = $testRow[0]['id'] ?? null;

        // Load questions by category from Supabase
        $questionsArr = $this->supabase->selectFrom('questions', ['category' => 'eq.' . $categoryName], [
            'order' => ['column' => 'id', 'asc' => true],
            'limit' => 50,
        ]);
        $questions = collect($questionsArr)->map(function ($q) {
            return (object) [
                'id' => $q['id'] ?? null,
                'question' => $q['question_text'] ?? '',
                'options' => json_encode([$q['option_a'] ?? '', $q['option_b'] ?? '', $q['option_c'] ?? '', $q['option_d'] ?? '']),
            ];
        });

        // Load timer minutes
        $minutes = 30;
        try {
            $settings = $this->supabase->selectFrom('test_settings', ['category' => 'eq.' . $categoryName], ['limit' => 1]);
            $minutes = (int) (($settings[0]['minutes'] ?? 30));
        } catch (\Throwable $e) {
            $minutes = 30; // default if table missing
        }

        return view('student.test', [
            'category' => (object) ['id' => $categoryId, 'name' => $categoryName],
            'questions' => $questions,
            'test_id' => $testId,
            'minutes' => $minutes,
        ]);
    }

    public function submitTest(Request $request, $id)
    {
        $request->validate([
            'test_id' => 'required|integer',
            'category_id' => 'required|integer',
            'answers' => 'required|array',
        ]);

        $userId = Auth::id();
        $testId = (int) $request->input('test_id');
        $answers = $request->input('answers');

        // Fetch correct answers from Supabase
        $questionIds = array_map('intval', array_keys($answers));
        $correctRows = [];
        foreach (array_chunk($questionIds, 50) as $chunk) {
            $ids = implode(',', $chunk);
            $rows = $this->supabase->selectFrom('questions', [ 'id' => 'in.(' . $ids . ')' ], ['select' => 'id,correct_answer']);
            $correctRows = array_merge($correctRows, $rows);
        }
        $byId = [];
        foreach ($correctRows as $r) { $byId[(int)($r['id'] ?? 0)] = (string)($r['correct_answer'] ?? ''); }

        $score = 0;
        $total = count($answers);
        foreach ($answers as $qid => $opt) {
            $corr = $byId[(int)$qid] ?? null;
            if ($corr !== null && (string)$corr === (string)$opt) { $score++; }
        }

        // Save per-answer rows
        foreach ($answers as $qid => $opt) {
            $this->supabase->insertInto('test_answers', [
                'test_id' => $testId,
                'question_id' => (int)$qid,
                'student_answer' => (string)$opt,
                'is_correct' => (string)($byId[(int)$qid] ?? '') === (string)$opt,
            ]);
        }

        // Update test summary
        $this->supabase->updateById('tests', $testId, [
            'score' => $score,
            'time_taken' => (int) $request->input('time_taken', 0),
        ]);

        return redirect()->route('student.results', ['id' => $testId]);
    }

    public function results($id)
    {
        $userId = Auth::id();
        $test = $this->supabase->selectFrom('tests', ['id' => 'eq.' . (int)$id], ['limit' => 1]);
        $row = $test[0] ?? null;
        $result = (object) [
            'test_id' => $row['id'] ?? null,
            'user_id' => $userId,
            'score' => (int)($row['score'] ?? 0),
            'total' => (int) ($this->supabase->selectFrom('test_answers', ['test_id' => 'eq.' . ((int)$id)], ['select' => 'id','limit' => 1000]) ? count($this->supabase->selectFrom('test_answers', ['test_id' => 'eq.' . ((int)$id)], ['select' => 'id','limit' => 1000])) : 0),
            'created_at' => $row['attempted_at'] ?? null,
        ];

        $all = $this->supabase->selectFrom('tests', [], ['select' => 'score,time_taken','limit' => 1000]);
        $peerAvg = 0;
        if (!empty($all)) {
            $scores = array_map(fn($r) => (float)($r['score'] ?? 0), $all);
            $peerAvg = count($scores) ? array_sum($scores) / count($scores) : 0;
        }

        $analysis = [];

        return view('student.results', compact('result', 'analysis', 'peerAvg'));
    }

    public function history()
    {
        $userId = Auth::id();
        $rows = $this->supabase->selectFrom('tests', ['student_id' => 'eq.' . $userId], [
            'order' => ['column' => 'attempted_at', 'asc' => false],
            'limit' => 100,
        ]);
        $history = array_map(function ($r) {
            return [
                'id' => $r['id'] ?? null,
                'attempted_at' => $r['attempted_at'] ?? null,
                'category' => $r['category'] ?? '',
                'score' => (int)($r['score'] ?? 0),
                'time_taken' => (int)($r['time_taken'] ?? 0),
            ];
        }, $rows);
        return view('student.history', compact('history'));
    }

    public function analytics()
    {
        $userId = Auth::id();
        $rows = DB::table('results')
            ->where('user_id', $userId)
            ->orderBy('created_at')
            ->limit(200)
            ->get(['test_id', 'score', 'total', 'created_at']);
        $trendLabels = collect($rows)->map(fn($r) => (string) Str::of((string) $r->created_at)->substr(0, 10))->all();
        $scoreTrends = collect($rows)->map(fn($r) => ($r->total ?? 0) > 0 ? round(($r->score / $r->total) * 100) : 0)->all();

        $testIds = collect($rows)->pluck('test_id')->filter()->unique()->values()->all();
        $categoryStats = ['Aptitude' => 0, 'Technical' => 0];
        if (!empty($testIds)) {
            $tests = DB::table('tests')->whereIn('id', $testIds)->get(['id', 'category_id']);
            $categoryIds = $tests->pluck('category_id')->unique()->values();
            $categories = DB::table('categories')->whereIn('id', $categoryIds)->pluck('name', 'id');
            $byTestId = collect($rows)->groupBy('test_id');
            foreach ($byTestId as $tid => $attempts) {
                $test = $tests->firstWhere('id', (int) $tid);
                if (!$test) continue;
                $name = $categories[$test->category_id] ?? null;
                if (!in_array($name, ['Aptitude','Technical'])) continue;
                $avg = $attempts->map(fn($r) => ($r->total ?? 0) > 0 ? ($r->score / $r->total) * 100 : 0)->avg();
                $categoryStats[$name] = round($categoryStats[$name] + ($avg ?? 0));
            }
        }

        return view('student.analytics', compact('trendLabels', 'scoreTrends', 'categoryStats'));
    }

    public function profile()
    {
        $user = Auth::user();
        return view('student.profile', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name' => 'required|string|min:2',
            'email' => 'required|email',
            'password' => 'nullable|min:6',
        ]);
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }
        $user->save();

        // Mirror to Supabase students table if present
        try {
            $exists = DB::table('students')->where('id', $user->id)->exists();
            if ($exists) {
                DB::table('students')->where('id', $user->id)->update([
                    'name' => $validated['name'],
                    'email' => $validated['email'],
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {}

        return back()->with('status', 'Profile updated!');
    }

    
}
