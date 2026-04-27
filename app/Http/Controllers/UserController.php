<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $users = User::query()
            ->when($request->input('search'), fn ($q, $s) => $q->where('name', 'like', "%{$s}%")->orWhere('username', 'like', "%{$s}%"))
            ->when($request->input('role'), fn ($q, $r) => $q->where('role', $r))
            ->when($request->input('status') !== null, function ($q) use ($request) {
                $q->where('is_active', $request->boolean('status'));
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Users/Index', [
            'users' => $users,
            'filters' => [
                'search' => $request->input('search', ''),
                'role' => $request->input('role', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'username' => ['required', 'string', 'min:3', 'max:20', 'regex:/^[a-zA-Z0-9._]+$/', 'unique:users,username'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string'],
            'role' => ['required', 'in:admin,kasir'],
            'password' => ['required', 'string', 'min:8', 'regex:/[a-z]/', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
        ], [
            'username.regex' => 'Username hanya boleh huruf, angka, titik, dan underscore.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.regex' => 'Password harus mengandung huruf besar, kecil, dan angka.',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'role' => $data['role'],
            'password' => $data['password'],
            'is_active' => true,
            'must_change_password' => false,
        ]);

        ActivityLogger::log('created', 'User', [
            'description' => "User {$user->name} ({$user->role}) dibuat",
            'reference_type' => 'user',
            'reference_id' => (string) $user->id,
            'new' => ['name' => $user->name, 'username' => $user->username, 'role' => $user->role],
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan!');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:2', 'max:255'],
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string'],
            'role' => ['required', 'in:admin,kasir'],
        ]);

        // Prevent admin from changing own role
        if ($user->id === auth()->id() && $data['role'] !== $user->role) {
            return back()->withErrors(['role' => 'Tidak bisa mengubah role sendiri.']);
        }

        $old = ['name' => $user->name, 'role' => $user->role, 'email' => $user->email];
        $user->update($data);

        ActivityLogger::log('updated', 'User', [
            'description' => "User {$user->name} diupdate",
            'reference_type' => 'user',
            'reference_id' => (string) $user->id,
            'old' => $old,
            'new' => ['name' => $user->name, 'role' => $user->role, 'email' => $user->email],
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui!');
    }

    public function destroy(User $user): RedirectResponse
    {
        // Prevent deleting self
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'Tidak bisa menghapus akun sendiri.']);
        }

        // Prevent deleting last admin
        if ($user->isAdmin() && User::where('role', 'admin')->where('id', '!=', $user->id)->count() === 0) {
            return back()->withErrors(['user' => 'Tidak bisa menghapus admin terakhir.']);
        }

        ActivityLogger::log('deleted', 'User', [
            'description' => "User {$user->name} ({$user->role}) dihapus",
            'reference_type' => 'user',
            'reference_id' => (string) $user->id,
        ]);

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User berhasil dihapus!');
    }

    public function resetPassword(User $user): RedirectResponse
    {
        $newPassword = Str::random(12);

        $user->update([
            'password' => $newPassword,
            'must_change_password' => true,
        ]);

        ActivityLogger::log('password_reset', 'User', [
            'description' => "Password user {$user->name} di-reset oleh admin",
            'reference_type' => 'user',
            'reference_id' => (string) $user->id,
        ]);

        return redirect()->route('users.index')
            ->with('success', "Password di-reset! Password baru: {$newPassword}")
            ->with('generated_password', $newPassword);
    }

    public function toggleActive(User $user): RedirectResponse
    {
        // Prevent deactivating self
        if ($user->id === auth()->id()) {
            return back()->withErrors(['user' => 'Tidak bisa menonaktifkan akun sendiri.']);
        }

        $user->update(['is_active' => ! $user->is_active]);

        $action = $user->is_active ? 'activated' : 'deactivated';
        ActivityLogger::log($action, 'User', [
            'description' => "User {$user->name} di-".($user->is_active ? 'aktifkan' : 'nonaktifkan'),
            'reference_type' => 'user',
            'reference_id' => (string) $user->id,
        ]);

        $msg = $user->is_active ? 'User diaktifkan!' : 'User dinonaktifkan!';

        return redirect()->route('users.index')->with('success', $msg);
    }
}
