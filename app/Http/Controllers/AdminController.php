<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Assessment;
use App\Models\Question;
use App\Models\StudentResult;
use App\Services\SupabaseService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    protected $supabaseService;

    public function __construct(SupabaseService $supabaseService)
    {
        $this->supabaseService = $supabaseService;
    }

    /**
     * Show admin dashboard
     */
    public function dashboard(): View
    {
        // Check if user is authenticated and is admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access.']);
        }

        // Basic user statistics
        $stats = [
            'total_students' => User::where('role', 'student')->count(),
            'approved_students' => User::approved()->count(),
            'pending_students' => User::pending()->count(),
            'rejected_students' => User::rejected()->count(),
        ];

        // Assessment and question statistics
        $stats['total_assessments'] = Assessment::count();
        $stats['active_assessments'] = Assessment::where('is_active', true)->count();
        $stats['total_questions'] = Question::count();
        $stats['total_attempts'] = StudentResult::count();

        // Calculate average score from Laravel models
        $averageScore = StudentResult::selectRaw('AVG((score / total_questions) * 100) as avg_percentage')
            ->value('avg_percentage');
        $stats['average_score'] = round($averageScore ?? 0, 2);

        // Get recent assessments with attempt counts
        $recentAssessments = Assessment::withCount('studentResults')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get assessment performance analytics
        $assessmentAnalytics = Assessment::with(['studentResults' => function($query) {
            $query->select('assessment_id', 'score', 'total_questions');
        }])
        ->withCount('studentResults')
        ->get()
        ->map(function($assessment) {
            $results = $assessment->studentResults;
            return [
                'id' => $assessment->id,
                'title' => $assessment->title,
                'category' => $assessment->category,
                'attempts' => $assessment->student_results_count,
                'avg_score' => round($results->avg('score') ?? 0, 2),
                'avg_percentage' => $results->count() > 0 
                    ? round($results->avg(function($result) {
                        return $result->total_questions > 0 ? ($result->score / $result->total_questions) * 100 : 0;
                    }), 2) : 0,
                'highest_score' => $results->max('score') ?? 0,
                'lowest_score' => $results->min('score') ?? 0,
            ];
        });

        // Get top performing students
        $topStudents = StudentResult::select('student_id')
            ->selectRaw('AVG((score / total_questions) * 100) as avg_percentage')
            ->selectRaw('COUNT(*) as attempts_count')
            ->with('student:id,name,email')
            ->groupBy('student_id')
            ->havingRaw('COUNT(*) >= 1')
            ->orderBy('avg_percentage', 'desc')
            ->limit(5)
            ->get();

        // Get category-wise performance
        $categoryPerformance = Assessment::select('category')
            ->join('student_results', 'assessments.id', '=', 'student_results.assessment_id')
            ->selectRaw('category, AVG((score / total_questions) * 100) as avg_percentage, COUNT(*) as total_attempts')
            ->groupBy('category')
            ->get()
            ->map(function($item) {
                return [
                    'category' => $item->category,
                    'avg_percentage' => round($item->avg_percentage ?? 0, 2),
                    'total_attempts' => $item->total_attempts ?? 0
                ];
            });

        $pendingStudents = User::pending()->orderBy('created_at', 'desc')->get();
        $recentApprovals = User::approved()->orderBy('admin_approved_at', 'desc')->limit(5)->get();

        return view('admin.dashboard', compact(
            'stats', 
            'pendingStudents', 
            'recentApprovals', 
            'recentAssessments', 
            'assessmentAnalytics', 
            'topStudents', 
            'categoryPerformance'
        ));
    }

    /**
     * Show pending students for approval
     */
    public function pendingStudents(): View
    {
        // Check if user is authenticated and is admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access.']);
        }

        $pendingStudents = User::pending()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.pending-students', compact('pendingStudents'));
    }

    /**
     * Show approved students
     */
    public function approvedStudents(): View
    {
        $approvedStudents = User::approved()
            ->orderBy('admin_approved_at', 'desc')
            ->paginate(10);

        return view('admin.approved-students', compact('approvedStudents'));
    }

    /**
     * Show rejected students
     */
    public function rejectedStudents(): View
    {
        $rejectedStudents = User::rejected()
            ->orderBy('admin_rejected_at', 'desc')
            ->paginate(10);

        return view('admin.rejected-students', compact('rejectedStudents'));
    }

    /**
     * Approve a student
     */
    public function approveStudent(Request $request, $id): RedirectResponse
    {
        // Check if user is authenticated and is admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access.']);
        }

        $student = User::where('id', $id)
            ->where('role', 'student')
            ->where('is_verified', true)
            ->where('is_approved', false)
            ->whereNull('admin_rejected_at')
            ->first();

        if (!$student) {
            return back()->withErrors(['error' => 'Student not found or already processed.']);
        }

        DB::beginTransaction();
        try {
            // Update local database
            $student->update([
                'is_approved' => true,
                'admin_approved_at' => now(),
                'status' => 'approved'
            ]);

            DB::commit();

            return back()->with('status', "Student {$student->name} has been approved successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to approve student. Please try again.']);
        }
    }

    /**
     * Reject a student
     */
    public function rejectStudent(Request $request, $id): RedirectResponse
    {
        // Check if user is authenticated and is admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access.']);
        }

        $request->validate([
            'rejection_reason' => 'nullable|string|max:500'
        ]);

        $student = User::where('id', $id)
            ->where('role', 'student')
            ->where('is_verified', true)
            ->where('is_approved', false)
            ->whereNull('admin_rejected_at')
            ->first();

        if (!$student) {
            return back()->withErrors(['error' => 'Student not found or already processed.']);
        }

        DB::beginTransaction();
        try {
            // Delete from Supabase if they have a supabase_id
            if ($student->supabase_id) {
                try {
                    $this->supabaseService->deleteUser($student->supabase_id);
                } catch (\Exception $e) {
                    // Log error but don't fail the transaction
                    \Log::warning("Failed to delete user from Supabase: " . $e->getMessage());
                }
            }

            // Delete from local database
            $student->delete();

            DB::commit();

            return back()->with('status', "Student {$student->name} has been rejected and removed from the system.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to reject student. Please try again.']);
        }
    }

    /**
     * Bulk approve students
     */
    public function bulkApprove(Request $request): RedirectResponse
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id'
        ]);

        $students = User::whereIn('id', $request->student_ids)
            ->where('role', 'student')
            ->where('is_verified', true)
            ->where('is_approved', false)
            ->whereNull('admin_rejected_at')
            ->get();

        if ($students->isEmpty()) {
            return back()->withErrors(['error' => 'No valid students found for approval.']);
        }

        DB::beginTransaction();
        try {
            foreach ($students as $student) {
                $student->update([
                    'is_approved' => true,
                    'admin_approved_at' => now(),
                    'status' => 'approved'
                ]);
            }

            DB::commit();

            return back()->with('status', "Successfully approved {$students->count()} students.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to approve students. Please try again.']);
        }
    }

    /**
     * Get student details for modal
     */
    public function getStudentDetails($id)
    {
        $student = User::where('id', $id)
            ->where('role', 'student')
            ->first();

        if (!$student) {
            return response()->json(['error' => 'Student not found'], 404);
        }

        return response()->json([
            'id' => $student->id,
            'name' => $student->name,
            'email' => $student->email,
            'created_at' => $student->created_at->format('M d, Y H:i'),
            'email_verified_at' => $student->email_verified_at ? $student->email_verified_at->format('M d, Y H:i') : null,
            'status' => $student->status,
            'is_pending' => $student->isPendingApproval(),
            'is_approved' => $student->isApproved(),
            'is_rejected' => $student->isRejected(),
        ]);
    }
}