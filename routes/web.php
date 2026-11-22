<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function (): void {
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])->name('login.store');

    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])->name('register.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/keluar', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/beranda', [HomeController::class, 'index'])->name('home');
    Route::get('/profil', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profil', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
});

// Halaman komponen untuk memeriksa konsistensi dan kontras; hanya di lokal.
if (app()->environment('local')) {
    Route::view('/styleguide', 'styleguide')->name('styleguide');
    Route::view('/styleguide/admin', 'styleguide-admin')->name('styleguide.admin');
}
