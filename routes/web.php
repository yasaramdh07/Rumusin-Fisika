<?php

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LessonController as AdminLessonController;
use App\Http\Controllers\Admin\LessonVideoController as AdminLessonVideoController;
use App\Http\Controllers\Admin\QuestionController as AdminQuestionController;
use App\Http\Controllers\Admin\TryoutController as AdminTryoutController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LessonController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TryoutController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('landing');

Route::get('/materi', [LessonController::class, 'index'])->name('lessons.index');
Route::get('/materi/{lesson:slug}', [LessonController::class, 'show'])->name('lessons.show');

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

    Route::get('/tryout', [TryoutController::class, 'index'])->name('tryouts.index');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    Route::get('/kategori', [AdminCategoryController::class, 'index'])->name('categories.index');
    Route::get('/kategori/tambah', [AdminCategoryController::class, 'create'])->name('categories.create');
    Route::post('/kategori', [AdminCategoryController::class, 'store'])->name('categories.store');
    Route::get('/kategori/{category}/ubah', [AdminCategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/kategori/{category}', [AdminCategoryController::class, 'update'])->name('categories.update');
    Route::delete('/kategori/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('/kategori/{category}/naik', [AdminCategoryController::class, 'moveUp'])->name('categories.up');
    Route::post('/kategori/{category}/turun', [AdminCategoryController::class, 'moveDown'])->name('categories.down');

    Route::post('/materi/pratinjau', [AdminLessonController::class, 'preview'])->name('lessons.preview');
    Route::get('/materi', [AdminLessonController::class, 'index'])->name('lessons.index');
    Route::get('/materi/tambah', [AdminLessonController::class, 'create'])->name('lessons.create');
    Route::post('/materi', [AdminLessonController::class, 'store'])->name('lessons.store');
    Route::get('/materi/{lesson}/ubah', [AdminLessonController::class, 'edit'])->name('lessons.edit');
    Route::put('/materi/{lesson}', [AdminLessonController::class, 'update'])->name('lessons.update');
    Route::delete('/materi/{lesson}', [AdminLessonController::class, 'destroy'])->name('lessons.destroy');

    Route::post('/materi/{lesson}/video', [AdminLessonVideoController::class, 'store'])->name('lessons.videos.store');
    Route::put('/materi/{lesson}/video/{video}', [AdminLessonVideoController::class, 'update'])->name('lessons.videos.update');
    Route::delete('/materi/{lesson}/video/{video}', [AdminLessonVideoController::class, 'destroy'])->name('lessons.videos.destroy');
    Route::post('/materi/{lesson}/video/{video}/naik', [AdminLessonVideoController::class, 'moveUp'])->name('lessons.videos.up');
    Route::post('/materi/{lesson}/video/{video}/turun', [AdminLessonVideoController::class, 'moveDown'])->name('lessons.videos.down');

    Route::post('/soal/pratinjau', [AdminQuestionController::class, 'preview'])->name('questions.preview');
    Route::get('/soal', [AdminQuestionController::class, 'index'])->name('questions.index');
    Route::get('/soal/tambah', [AdminQuestionController::class, 'create'])->name('questions.create');
    Route::post('/soal', [AdminQuestionController::class, 'store'])->name('questions.store');
    Route::get('/soal/{question}/ubah', [AdminQuestionController::class, 'edit'])->name('questions.edit');
    Route::put('/soal/{question}', [AdminQuestionController::class, 'update'])->name('questions.update');
    Route::delete('/soal/{question}', [AdminQuestionController::class, 'destroy'])->name('questions.destroy');

    Route::get('/tryout', [AdminTryoutController::class, 'index'])->name('tryouts.index');
    Route::get('/tryout/tambah', [AdminTryoutController::class, 'create'])->name('tryouts.create');
    Route::post('/tryout', [AdminTryoutController::class, 'store'])->name('tryouts.store');
    Route::get('/tryout/{tryout}/ubah', [AdminTryoutController::class, 'edit'])->name('tryouts.edit');
    Route::put('/tryout/{tryout}', [AdminTryoutController::class, 'update'])->name('tryouts.update');
    Route::delete('/tryout/{tryout}', [AdminTryoutController::class, 'destroy'])->name('tryouts.destroy');
    Route::post('/tryout/{tryout}/aktifkan', [AdminTryoutController::class, 'activate'])->name('tryouts.activate');
    Route::post('/tryout/{tryout}/soal/{question}/naik', [AdminTryoutController::class, 'moveQuestionUp'])->name('tryouts.questions.up');
    Route::post('/tryout/{tryout}/soal/{question}/turun', [AdminTryoutController::class, 'moveQuestionDown'])->name('tryouts.questions.down');
});

// Halaman komponen untuk memeriksa konsistensi dan kontras; hanya di lokal.
if (app()->environment('local')) {
    Route::view('/styleguide', 'styleguide')->name('styleguide');
    Route::view('/styleguide/admin', 'styleguide-admin')->name('styleguide.admin');
}
