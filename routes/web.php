<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Resident\BarangayPermitController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');



Route::prefix('resident')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Resident/Home');
    })->name('resident.dashboard');

    Route::get('/barangay-business-permit', function () {
        return Inertia::render('Resident/BarangayBusinessPermit');
    })->name('resident.barangay-business-permit');

    Route::get('/barangay-permit/create', [BarangayPermitController::class, 'create'])->name('barangay-permit.create');
    Route::post('/barangay-permit', [BarangayPermitController::class, 'store'])->name('barangay-permit.store');
    Route::get('/psgc/island-groups/{code}/barangays', [BarangayPermitController::class, 'barangaysByIslandGroup'])->name('psgc.barangays.by-island-group');
    Route::get('/psgc/cities/{code}/barangays-min', [\App\Http\Controllers\Resident\BarangayPermitController::class, 'barangaysByCity'])->name('psgc.barangays.min');

    // New PSGC endpoints
    Route::get('/psgc/regions', [BarangayPermitController::class, 'regions'])->name('psgc.regions');
    Route::get('/psgc/regions/{code}/provinces', [BarangayPermitController::class, 'provincesByRegion'])->name('psgc.provinces.by-region');
    Route::get('/psgc/provinces/{code}/cities', [BarangayPermitController::class, 'citiesByProvince'])->name('psgc.cities.by-province');
    Route::get('/psgc/regions/{code}/cities', [BarangayPermitController::class, 'citiesByRegion'])->name('psgc.cities.by-region');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return redirect()->route('resident.dashboard');
    })->name('dashboard');
});

Route::prefix('admin')->group(function () {
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::get('home', function () {
            return Inertia::render('Admin/Home');
        })->name('admin.home');

        Route::get('dashboard', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('admin.dashboard');
    });
});



require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
