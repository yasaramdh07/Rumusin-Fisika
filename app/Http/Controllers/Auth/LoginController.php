<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    private const MAX_ATTEMPTS = 5;

    private const DECAY_SECONDS = 60;

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'identity' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'identity.required' => 'Username atau email wajib diisi.',
            'identity.string' => 'Username atau email tidak valid.',
            'identity.max' => 'Username atau email terlalu panjang.',
            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Password tidak valid.',
        ]);

        $identity = trim($validated['identity']);
        $throttleKey = $this->throttleKey($identity, (string) $request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, self::MAX_ATTEMPTS)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'identity' => "Terlalu banyak percobaan masuk. Tunggu {$seconds} detik lalu coba lagi.",
            ]);
        }

        $field = filter_var($identity, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $lookupValue = mb_strtolower($identity);

        if (! Auth::attempt([$field => $lookupValue, 'password' => $validated['password'], 'is_active' => true])) {
            RateLimiter::hit($throttleKey, self::DECAY_SECONDS);

            throw ValidationException::withMessages([
                'identity' => 'Username, email, atau password belum cocok. Periksa kembali lalu coba lagi.',
            ]);
        }

        RateLimiter::clear($throttleKey);
        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();
        $defaultTarget = $user->isAdmin() ? route('admin.dashboard') : route('home');

        return redirect()->intended($defaultTarget);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Kamu sudah keluar.');
    }

    private function throttleKey(string $identity, string $ip): string
    {
        return 'login:'.mb_strtolower($identity).'|'.$ip;
    }
}
