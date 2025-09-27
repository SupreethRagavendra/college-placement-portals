<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminStudentController extends Controller
{
    /**
     * Show pending students page
     */
    public function pending(): View
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $students = User::pending()
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.students.pending', compact('students'));
    }

    /**
     * Approve student (sets is_approved = true)
     */
    public function approve($id): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $student = User::where('id', $id)
            ->where('role', 'student')
            ->where('is_verified', true)
            ->where('is_approved', false)
            ->first();
            
        if (!$student) {
            return back()->withErrors(['error' => 'Student not found or already processed']);
        }

        DB::beginTransaction();
        try {
            $student->update([
                'is_approved' => true,
                'admin_approved_at' => now(),
                'status' => 'approved',
            ]);
            DB::commit();
            return back()->with('status', "Student {$student->name} has been approved successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to approve student']);
        }
    }

    /**
     * Reject student (mark as rejected)
     */
    public function reject($id): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $student = User::where('id', $id)
            ->where('role', 'student')
            ->whereNull('admin_rejected_at')
            ->first();
            
        if (!$student) {
            return back()->withErrors(['error' => 'Student not found or already processed']);
        }

        DB::beginTransaction();
        try {
            $studentName = $student->name;
            $student->update([
                'admin_rejected_at' => now(),
                'status' => 'rejected'
            ]);
            DB::commit();
            return back()->with('status', "Student {$studentName} has been rejected.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to reject student']);
        }
    }
}


