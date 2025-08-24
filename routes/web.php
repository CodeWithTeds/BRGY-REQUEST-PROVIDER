<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('resident/dashboard', function () {
    return Inertia::render('Resident/Home');
})->middleware(['auth'])->name('resident.dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('resident.dashboard');
    })->name('dashboard');
});

Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('admin/home', function () {
        return Inertia::render('Admin/Home');
    })->name('admin.home');

    Route::get('admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard');
    })->name('admin.dashboard');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
