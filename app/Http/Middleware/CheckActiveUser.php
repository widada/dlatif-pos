<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckActiveUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user) {
            return $next($request);
        }

        // Force logout if account deactivated
        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->withErrors(['username' => 'Akun Anda dinonaktifkan. Hubungi admin.']);
        }

        // Redirect to change password if required (skip if already on that route)
        if ($user->must_change_password && ! $request->routeIs('profile.change-password', 'profile.update-password', 'logout')) {
            return redirect()->route('profile.change-password')
                ->with('warning', 'Anda wajib mengganti password sebelum melanjutkan.');
        }

        return $next($request);
    }
}
