<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AuthController;
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

// Landing page
Route::get('/', function () {
    return view('landing');
})->name('landing');

// Custom authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

// Email verification routes
Route::get('/verify', function () {
    return view('auth.verify');
})->name('verification.notice.custom');

Route::get('/verify-email/{token}', [AuthController::class, 'verifyEmail'])
    ->middleware('signed')
    ->name('verify.email.custom');

// Redirect authenticated users based on role
Route::get('/dashboard', function () {
    if (Auth::user()->role === 'admin') {
        return redirect()->route('admin.dashboard');
    } else {
        return redirect()->route('student.dashboard');
    }
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});

// Student routes
Route::middleware(['auth', 'role:student'])->prefix('student')->group(function () {
    Route::get('/dashboard', function () {
        return view('student.dashboard');
    })->name('student.dashboard');
});

// Profile routes (accessible by both roles)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
});
