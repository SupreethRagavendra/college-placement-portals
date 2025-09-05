<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'in:admin,student'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        $token = bin2hex(random_bytes(32));
        DB::table('email_verification_tokens')->insert([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => Carbon::now()->addHour(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $signedUrl = URL::temporarySignedRoute('verify.email.custom', now()->addHour(), ['token' => $token]);

        Mail::send('emails.verify', ['user' => $user, 'url' => $signedUrl], function ($message) use ($user) {
            $message->to($user->email)->subject('Verify your email');
        });

        return redirect()->route('verification.notice.custom')->with('status', 'verification-link-sent');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => trans('auth.failed')])->withInput();
        }

        $request->session()->regenerate();

        if (is_null(Auth::user()->email_verified_at)) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('verification.notice.custom')->withErrors(['email' => 'Please verify your email before login.']);
        }

        return $this->redirectToDashboard();
    }

    public function verifyEmail(Request $request, string $token): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid or expired verification link.']);
        }

        $record = DB::table('email_verification_tokens')
            ->where('token', $token)
            ->where('expires_at', '>', now())
            ->first();

        if (! $record) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid or expired verification link.']);
        }

        $user = User::find($record->user_id);
        if (! $user) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid verification request.']);
        }

        if (is_null($user->email_verified_at)) {
            $user->forceFill(['email_verified_at' => now()])->save();
        }

        DB::table('email_verification_tokens')->where('token', $token)->delete();

        return redirect()->route('login')->with('status', 'Email verified successfully. You can now login.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('landing');
    }

    private function redirectToDashboard(): RedirectResponse
    {
        $role = Auth::user()->role;
        if ($role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('student.dashboard');
    }
}


