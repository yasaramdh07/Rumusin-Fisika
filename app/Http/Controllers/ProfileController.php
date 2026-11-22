<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        $attempts = $user->attempts()
            ->with('tryout')
            ->whereNotNull('finished_at')
            ->orderByDesc('finished_at')
            ->get();

        $bestScore = $attempts->max('score');

        return view('profile.show', [
            'user' => $user,
            'attempts' => $attempts,
            'bestScore' => $bestScore,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $wantsPasswordChange = $request->filled('password')
            || $request->filled('current_password')
            || $request->filled('password_confirmation');

        $rules = [
            'name' => ['required', 'string', 'max:100'],
        ];

        if ($wantsPasswordChange) {
            $rules['current_password'] = ['required', 'string'];
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules, [
            'name.required' => 'Nama wajib diisi.',
            'name.max' => 'Nama maksimal 100 karakter.',
            'current_password.required' => 'Isi password saat ini untuk mengganti password.',
            'password.required' => 'Isi password baru minimal 8 karakter.',
            'password.min' => 'Password baru minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password baru belum sama.',
        ]);

        if ($wantsPasswordChange && ! Hash::check($validated['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Password saat ini belum cocok. Periksa kembali.',
            ]);
        }

        $user->name = trim($validated['name']);

        if ($wantsPasswordChange) {
            $user->password = $validated['password'];
        }

        $user->save();

        return redirect()->route('profile.show')->with('status', 'Perubahan profil disimpan.');
    }
}
