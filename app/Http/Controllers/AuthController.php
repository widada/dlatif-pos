<?php

namespace App\Http\Controllers;

use App\Models\LoginAttempt;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class AuthController extends Controller
{
    public function showLogin(): Response|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('Auth/Login');
    }

    public function login(Request $request): RedirectResponse
    {
        $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        $username = $request->input('username');
        $user = User::where('username', $username)->first();

        // Check lockout
        if ($user && $user->isLocked()) {
            $minutes = now()->diffInMinutes($user->locked_until) + 1;
            $this->logLoginAttempt($username, false);

            return back()->withErrors([
                'username' => "Akun dikunci. Coba lagi dalam {$minutes} menit.",
            ])->onlyInput('username');
        }

        // Check active
        if ($user && ! $user->is_active) {
            $this->logLoginAttempt($username, false);

            return back()->withErrors([
                'username' => 'Akun Anda dinonaktifkan. Hubungi admin.',
            ])->onlyInput('username');
        }

        if (Auth::attempt(['username' => $username, 'password' => $request->input('password')], $request->boolean('remember'))) {
            $request->session()->regenerate();

            // Reset failed attempts
            $user = Auth::user();
            $user->update([
                'failed_login_attempts' => 0,
                'locked_until' => null,
                'last_login_at' => now(),
            ]);

            $this->logLoginAttempt($username, true);

            ActivityLogger::log('login', 'Auth', [
                'description' => "User {$user->name} berhasil login",
            ]);

            return redirect()->intended(route('dashboard'));
        }

        // Failed login
        $this->logLoginAttempt($username, false);

        if ($user) {
            $attempts = $user->failed_login_attempts + 1;
            $updateData = ['failed_login_attempts' => $attempts];

            if ($attempts >= 5) {
                $updateData['locked_until'] = now()->addMinutes(15);
            }

            $user->update($updateData);
        }

        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->onlyInput('username');
    }

    public function logout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user) {
            ActivityLogger::log('logout', 'Auth', [
                'description' => "User {$user->name} logout",
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function logLoginAttempt(string $username, bool $success): void
    {
        LoginAttempt::create([
            'username' => $username,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'success' => $success,
            'created_at' => now(),
        ]);
    }
}
