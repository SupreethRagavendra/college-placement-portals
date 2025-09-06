<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminStudentController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\AdminAssessmentController;
use App\Http\Controllers\AdminQuestionController;
use App\Http\Controllers\StudentAssessmentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Test database connection
Route::get('/test-db', function () {
    try {
        $pdo = new PDO(
            "pgsql:host=db.wkqbukidxmzbgwauncrl.supabase.co;port=5432;dbname=postgres;sslmode=require",
            "postgres",
            "Supreeeth24#"
        );
        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $pdo->query("SELECT version()");
        $version = $stmt->fetchColumn();
        
        return response()->json([
            'status' => 'success',
            'message' => 'Connected to Supabase PostgreSQL successfully!',
            'version' => $version
        ]);
        
    } catch (PDOException $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Connection failed: ' . $e->getMessage()
        ]);
    }
});

// Test Supabase configuration
Route::get('/test-supabase', function () {
    $config = [
        'supabase_url' => config('supabase.url'),
        'supabase_anon_key' => config('supabase.anon_key'),
        'supabase_service_role_key' => config('supabase.service_role_key'),
    ];
    
    $issues = [];
    
    if (str_contains($config['supabase_url'], 'your-project')) {
        $issues[] = 'SUPABASE_URL is not configured (still using placeholder)';
    }
    
    if (str_contains($config['supabase_anon_key'], 'your_anon_key_here')) {
        $issues[] = 'SUPABASE_ANON_KEY is not configured (still using placeholder)';
    }
    
    if (str_contains($config['supabase_service_role_key'], 'your_service_role_key_here')) {
        $issues[] = 'SUPABASE_SERVICE_ROLE_KEY is not configured (still using placeholder)';
    }
    
    return response()->json([
        'status' => empty($issues) ? 'success' : 'error',
        'config' => $config,
        'issues' => $issues,
        'message' => empty($issues) ? 'Supabase configuration looks good!' : 'Please configure your Supabase API keys in .env file'
    ]);
});

// Landing page
Route::get('/', function () {
    return view('landing');
})->name('landing');

// Authentication routes (no email verification required)
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Redirect authenticated users based on role
Route::get('/dashboard', function () {
    if (Auth::user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } else {
        return redirect()->route('student.dashboard');
    }
})->middleware(['auth'])->name('dashboard');

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

    // Student verification
    Route::get('/students/pending', [AdminStudentController::class, 'pending'])->name('admin.pending-students');
    Route::post('/students/{id}/approve', [AdminStudentController::class, 'approve'])->name('admin.approve-student');
    Route::post('/students/{id}/reject', [AdminStudentController::class, 'reject'])->name('admin.reject-student');

    // Student lists (for dashboard links)
    Route::get('/students/approved', [AdminController::class, 'approvedStudents'])->name('admin.approved-students');
    Route::get('/students/rejected', [AdminController::class, 'rejectedStudents'])->name('admin.rejected-students');

    // Assessment Management
    Route::resource('assessments', AdminAssessmentController::class)->names([
        'index' => 'admin.assessments.index',
        'create' => 'admin.assessments.create',
        'store' => 'admin.assessments.store',
        'show' => 'admin.assessments.show',
        'edit' => 'admin.assessments.edit',
        'update' => 'admin.assessments.update',
        'destroy' => 'admin.assessments.destroy',
    ]);
    
    // Assessment Question Management
    Route::get('/assessments/{assessment}/questions', [AdminAssessmentController::class, 'questions'])
        ->name('admin.assessments.questions');
    Route::get('/assessments/{assessment}/add-question', [AdminAssessmentController::class, 'addQuestion'])
        ->name('admin.assessments.add-question');
    Route::post('/assessments/{assessment}/store-question', [AdminAssessmentController::class, 'storeQuestion'])
        ->name('admin.assessments.store-question');
    Route::post('/assessments/{assessment}/assign-question', [AdminAssessmentController::class, 'assignQuestion'])
        ->name('admin.assessments.assign-question');
    Route::get('/assessments/{assessment}/questions/{question}/edit', [AdminAssessmentController::class, 'editQuestion'])
        ->name('admin.assessments.edit-question');
    Route::put('/assessments/{assessment}/questions/{question}/update', [AdminAssessmentController::class, 'updateQuestion'])
        ->name('admin.assessments.update-question');
    Route::delete('/assessments/{assessment}/questions/{question}/delete', [AdminAssessmentController::class, 'deleteQuestion'])
        ->name('admin.assessments.delete-question');

    // Question Management
    Route::resource('questions', AdminQuestionController::class)->names([
        'index' => 'admin.questions.index',
        'create' => 'admin.questions.create', 
        'store' => 'admin.questions.store',
        'show' => 'admin.questions.show',
        'edit' => 'admin.questions.edit',
        'update' => 'admin.questions.update',
        'destroy' => 'admin.questions.destroy',
    ]);
    Route::post('/questions/bulk', [AdminQuestionController::class, 'bulk'])->name('admin.questions.bulk');
    Route::get('/questions/import', [AdminQuestionController::class, 'import'])->name('admin.questions.import');
    Route::post('/questions/import', [AdminQuestionController::class, 'processImport'])->name('admin.questions.process-import');

    // Reports
    Route::get('/reports', [AdminReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/reports/assessments/{assessment}', [AdminReportController::class, 'assessmentDetails'])
        ->name('admin.reports.assessment-details');
    Route::get('/reports/students', [AdminReportController::class, 'studentPerformance'])
        ->name('admin.reports.student-performance');
    Route::get('/reports/categories', [AdminReportController::class, 'categoryAnalysis'])
        ->name('admin.reports.category-analysis');
    Route::get('/reports/questions/{assessment}', [AdminReportController::class, 'questionAnalysis'])
        ->name('admin.reports.question-analysis');
    Route::get('/reports/export', [AdminReportController::class, 'exportCsv'])->name('admin.reports.export');
});

// Student routes
Route::middleware(['auth', 'role:student'])->prefix('student')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\StudentController::class, 'dashboard'])->name('student.dashboard');
    
    // Assessment routes
    Route::get('/assessments', [StudentAssessmentController::class, 'index'])->name('student.assessments');
    Route::get('/assessments/{assessment}', [StudentAssessmentController::class, 'show'])->name('student.assessment.show');
    Route::get('/assessments/{assessment}/start', [StudentAssessmentController::class, 'start'])->name('student.assessment.start');
    Route::post('/assessments/{assessment}/submit', [StudentAssessmentController::class, 'submit'])->name('student.assessment.submit');
    Route::get('/assessments/{assessment}/result', [StudentAssessmentController::class, 'result'])->name('student.assessment.result');
    Route::get('/assessment-history', [StudentAssessmentController::class, 'history'])->name('student.assessment.history');
    Route::get('/analytics', [StudentAssessmentController::class, 'analytics'])->name('student.assessments.analytics');
    
    // Legacy routes (keeping for backward compatibility)
    Route::get('/categories', [\App\Http\Controllers\StudentController::class, 'categories'])->name('student.categories');
    Route::get('/test/{id}', [\App\Http\Controllers\StudentController::class, 'test'])->name('student.test');
    Route::post('/test/{id}/submit', [\App\Http\Controllers\StudentController::class, 'submitTest'])->name('student.test.submit');
    Route::get('/results/{id}', [\App\Http\Controllers\StudentController::class, 'results'])->name('student.results');
    Route::get('/profile', [\App\Http\Controllers\StudentController::class, 'profile'])->name('student.profile');
    Route::post('/profile', [\App\Http\Controllers\StudentController::class, 'updateProfile'])->name('student.profile.update');
    Route::get('/history', [\App\Http\Controllers\StudentController::class, 'history'])->name('student.history');
});

// Profile routes (accessible by both roles)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
