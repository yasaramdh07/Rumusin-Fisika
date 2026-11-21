<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Halaman komponen untuk memeriksa konsistensi dan kontras; hanya di lokal.
if (app()->environment('local')) {
    Route::view('/styleguide', 'styleguide')->name('styleguide');
    Route::view('/styleguide/admin', 'styleguide-admin')->name('styleguide.admin');
}