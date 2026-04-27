<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function show(): Response
    {
        $user = Auth::user();

        return Inertia::render('Profile/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'last_login_at' => $user->last_login_at,
                'created_at' => $user->created_at,
            ],
            'mustChangePassword' => $user->must_change_password,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['nullable', 'email', 'unique:users,email,'.$user->id],
            'phone' => ['nullable', 'string'],
        ]);

        $user->update($data);

        return back()->with('success', 'Profil berhasil diperbarui!');
    }

    public function changePassword(): Response
    {
        return Inertia::render('Profile/ChangePassword', [
            'mustChange' => Auth::user()->must_change_password,
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $rules = [
            'new_password' => ['required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/', 'confirmed'],
        ];

        // Only require current password if not forced change
        if (! $user->must_change_password) {
            $rules['current_password'] = ['required'];
        }

        $request->validate($rules, [
            'new_password.min' => 'Password baru minimal 8 karakter.',
            'new_password.regex' => 'Password harus mengandung huruf besar, kecil, dan angka.',
            'new_password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        // Verify current password if not forced change
        if (! $user->must_change_password) {
            if (! Hash::check($request->input('current_password'), $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama salah.']);
            }
        }

        // Prevent same as username
        if ($request->input('new_password') === $user->username) {
            return back()->withErrors(['new_password' => 'Password tidak boleh sama dengan username.']);
        }

        $user->update([
            'password' => $request->input('new_password'),
            'must_change_password' => false,
        ]);

        ActivityLogger::log('password_changed', 'Auth', [
            'description' => "User {$user->name} mengganti password",
        ]);

        return redirect()->route('dashboard')->with('success', 'Password berhasil diubah!');
    }
}
