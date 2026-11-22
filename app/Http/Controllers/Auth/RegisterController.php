<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'username' => is_string($request->input('username')) ? trim($request->input('username')) : $request->input('username'),
            'email' => is_string($request->input('email')) ? mb_strtolower(trim($request->input('email'))) : $request->input('email'),
        ]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'min:3', 'max:20', 'regex:/^[a-z0-9_]+$/', 'unique:users,username'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'username.required' => 'Username wajib diisi.',
            'username.min' => 'Username minimal 3 karakter.',
            'username.max' => 'Username maksimal 20 karakter.',
            'username.regex' => 'Username hanya boleh memakai huruf kecil, angka, dan garis bawah (_).',
            'username.unique' => 'Username sudah dipakai. Pilih username lain.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email belum benar. Contoh: nama@sekolah.id.',
            'email.max' => 'Email maksimal 255 karakter.',
            'email.unique' => 'Email sudah terdaftar. Silakan masuk atau pakai email lain.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password belum sama dengan password.',
        ]);

        $user = new User([
            'name' => trim($validated['name']),
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);
        $user->forceFill([
            'role' => User::ROLE_USER,
            'is_active' => true,
        ])->save();

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('home')->with('status', 'Akun berhasil dibuat. Selamat belajar!');
    }
}
