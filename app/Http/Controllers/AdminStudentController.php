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

        $students = User::where('role', 'student')
            ->where('is_verified', false)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.students.pending', compact('students'));
    }

    /**
     * Approve student (sets is_verified = true)
     */
    public function approve($id): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $student = User::where('id', $id)->where('role', 'student')->first();
        if (!$student) {
            return back()->withErrors(['error' => 'Student not found']);
        }

        DB::beginTransaction();
        try {
            $student->update([
                'is_verified' => true,
                'is_approved' => true,
                'admin_approved_at' => now(),
                'status' => 'approved',
            ]);
            DB::commit();
            return back()->with('status', 'Student approved');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Failed to approve student']);
        }
    }

    /**
     * Reject student (delete from DB)
     */
    public function reject($id): RedirectResponse
    {
        if (!Auth::check() || !Auth::user()->isAdmin()) {
            abort(403);
        }

        $student = User::where('id', $id)->where('role', 'student')->first();
        if (!$student) {
            return back()->withErrors(['error' => 'Student not found']);
        }

        try {
            $student->delete();
            return back()->with('status', 'Student rejected and removed');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to reject student']);
        }
    }
}


