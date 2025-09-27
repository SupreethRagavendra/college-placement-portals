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

            // Send approval email asynchronously (non-blocking)
            $this->sendStatusEmailAsync($student, 'approved');

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
            // Mark student as rejected instead of deleting
            $student->update([
                'admin_rejected_at' => now(),
                'status' => 'rejected',
                'rejection_reason' => $request->rejection_reason
            ]);

            DB::commit();

            // Send rejection email asynchronously (non-blocking)
            $this->sendStatusEmailAsync($student, 'rejected', $request->rejection_reason);

            return back()->with('status', "Student {$student->name} has been rejected.");

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
            $approvedCount = 0;
            foreach ($students as $student) {
                $student->update([
                    'is_approved' => true,
                    'admin_approved_at' => now(),
                    'status' => 'approved'
                ]);
                
                // Send approval email asynchronously for each student
                $this->sendStatusEmailAsync($student, 'approved');
                $approvedCount++;
            }

            DB::commit();

            return back()->with('status', "Successfully approved {$approvedCount} students.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to approve students. Please try again.']);
        }
    }

    /**
     * Bulk reject students
     */
    public function bulkReject(Request $request): RedirectResponse
    {
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:users,id'
        ]);

        $students = User::whereIn('id', $request->student_ids)
            ->where('role', 'student')
            ->whereNull('admin_rejected_at')
            ->get();

        if ($students->isEmpty()) {
            return back()->withErrors(['error' => 'No valid students found for rejection.']);
        }

        DB::beginTransaction();
        try {
            $rejectedCount = 0;
            foreach ($students as $student) {
                // Mark student as rejected instead of deleting
                $student->update([
                    'admin_rejected_at' => now(),
                    'status' => 'rejected'
                ]);
                
                // Send rejection email asynchronously for each student
                $this->sendStatusEmailAsync($student, 'rejected');
                $rejectedCount++;
            }

            DB::commit();

            return back()->with('status', "Successfully rejected {$rejectedCount} students.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to reject students. Please try again.']);
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
            'admin_rejected_at' => $student->admin_rejected_at ? $student->admin_rejected_at->format('M d, Y H:i') : null,
            'rejection_reason' => $student->rejection_reason,
            'status' => $student->status,
            'is_pending' => $student->isPendingApproval(),
            'is_approved' => $student->isApproved(),
            'is_rejected' => $student->isRejected(),
        ]);
    }

    /**
     * Restore a rejected student back to pending status
     */
    public function restoreStudent(Request $request, $id): RedirectResponse
    {
        // Check if user is authenticated and is admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access.']);
        }

        $student = User::where('id', $id)
            ->where('role', 'student')
            ->whereNotNull('admin_rejected_at')
            ->first();

        if (!$student) {
            return back()->withErrors(['error' => 'Student not found or not rejected.']);
        }

        DB::beginTransaction();
        try {
            // Restore student to pending status
            $student->update([
                'admin_rejected_at' => null,
                'rejection_reason' => null,
                'status' => 'pending',
                'is_approved' => false
            ]);

            DB::commit();

            return back()->with('status', "Student {$student->name} has been restored to pending status.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to restore student. Please try again.']);
        }
    }

    /**
     * Revoke access for an approved student (mark as rejected)
     */
    public function revokeStudent(Request $request, $id): RedirectResponse
    {
        // Check if user is authenticated and is admin
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            return redirect()->route('login')->withErrors(['error' => 'Unauthorized access.']);
        }

        $student = User::where('id', $id)
            ->where('role', 'student')
            ->where('is_approved', true)
            ->whereNull('admin_rejected_at')
            ->first();

        if (!$student) {
            return back()->withErrors(['error' => 'Student not found or already processed.']);
        }

        DB::beginTransaction();
        try {
            // Revoke access by marking as rejected
            $student->update([
                'is_approved' => false,
                'admin_rejected_at' => now(),
                'status' => 'rejected',
                'rejection_reason' => 'Access revoked by administrator'
            ]);

            DB::commit();

            // Send rejection email asynchronously (non-blocking)
            $this->sendStatusEmailAsync($student, 'rejected', 'Access revoked by administrator');

            return back()->with('status', "Access for {$student->name} has been revoked successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to revoke access. Please try again.']);
        }
    }

    /**
     * Send status email asynchronously to student
     */
    private function sendStatusEmailAsync(User $student, string $status, ?string $rejectionReason = null): void
    {
        try {
            // Send email notification asynchronously
            $promise = $this->supabaseService->sendStatusEmailAsync(
                $student->email,
                $student->name,
                $status,
                $rejectionReason
            );

            // Handle the promise completion (optional - for logging)
            if ($promise) {
                $promise->then(
                    function ($response) use ($student, $status) {
                        \Log::info("Status email sent successfully", [
                            'student_email' => $student->email,
                            'status' => $status,
                            'timestamp' => now()
                        ]);
                    },
                    function ($exception) use ($student, $status) {
                        \Log::error("Failed to send status email", [
                            'student_email' => $student->email,
                            'status' => $status,
                            'error' => $exception->getMessage(),
                            'timestamp' => now()
                        ]);
                    }
                );
            }
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            \Log::error("Error initiating status email", [
                'student_email' => $student->email,
                'status' => $status,
                'error' => $e->getMessage(),
                'timestamp' => now()
            ]);
        }
    }
}