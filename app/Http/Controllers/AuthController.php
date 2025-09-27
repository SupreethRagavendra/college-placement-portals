<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\SupabaseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class AuthController extends Controller
{
    protected $supabaseService;

    public function __construct(SupabaseService $supabaseService)
    {
        $this->supabaseService = $supabaseService;
    }

    /**
     * Show registration form
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * Handle user registration
     * Note: Public registration is for students only.
     * Admin accounts should be created manually or through a separate admin registration process.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['nullable', 'in:student,admin'],
        ]);

        // Default to student role if not provided
        $role = $validated['role'] ?? 'student';

        try {
            // Create user in local database
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => $role,
                'is_verified' => true, // No email verification required
                'is_approved' => $role === 'admin' ? true : false, // Auto-approve admins
                'email_verified_at' => now(),
                'status' => $role === 'admin' ? 'approved' : 'pending',
            ]);

            // Try to create user in Supabase (optional - for future use)
            try {
                $supabaseResponse = $this->supabaseService->signUp(
                    $validated['email'],
                    $validated['password'],
                    [
                        'name' => $validated['name'],
                        'role' => $role
                    ]
                );

                if (isset($supabaseResponse['id'])) {
                    $user->update(['supabase_id' => $supabaseResponse['id']]);
                }
            } catch (\Exception $e) {
                // Log error but don't fail registration
                \Log::warning('Failed to create user in Supabase: ' . $e->getMessage());
            }

            if ($role === 'admin') {
                $user->update([
                    'admin_approved_at' => now(),
                ]);
                
                return redirect()->route('login')
                    ->with('status', 'Admin account created successfully! You can now login.');
            } else {
                return redirect()->route('login')
                    ->with('status', 'Student registration successful! Your account is pending admin approval.');
            }

        } catch (\Exception $e) {
            return back()->withErrors(['email' => $e->getMessage()]);
        }
    }

    /**
     * Show login form
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * Handle user login
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Use Laravel's built-in authentication instead of Supabase
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            
            // Check approval status
            if ($user->canLogin()) {
                $request->session()->regenerate();
                return $this->redirectToDashboard();
            } elseif ($user->isPendingApproval()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => 'Your account is pending admin approval.']);
            } elseif ($user->isRejected()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => 'Your account has been rejected. Please contact support.']);
            }
        }

        return back()->withErrors(['email' => 'Invalid credentials.']);
    }

    /**
     * Handle logout
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('landing');
    }

    /**
     * Redirect to appropriate dashboard
     */
    private function redirectToDashboard(): RedirectResponse
    {
        $user = Auth::user();
        
        if ($user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        } else {
            return redirect()->route('student.dashboard');
        }
    }
}
