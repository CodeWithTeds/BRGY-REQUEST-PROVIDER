<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Resident\BarangayPermitController;
use App\Http\Controllers\Resident\BarangayClearanceController;
use App\Http\Controllers\Resident\CertificateOfResidencyController;

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

    Route::get('/barangay-clearance', function () {
        return Inertia::render('Resident/BarangayClearance');
    })->name('resident.barangay-clearance');

    Route::get('certificate-of-residency', [CertificateOfResidencyController::class, 'create'])->name('resident.certificate-of-residency.create');
    Route::post('certificate-of-residency', [CertificateOfResidencyController::class, 'store'])->name('resident.certificate-of-residency.store');

    Route::get('/barangay-permit/create', [BarangayPermitController::class, 'create'])->name('barangay-permit.create');
    Route::post('/barangay-permit', [BarangayPermitController::class, 'store'])->name('barangay-permit.store');

    Route::get('/barangay-clearance/create', [BarangayClearanceController::class, 'create'])->name('barangay-clearance.create');
    Route::post('/barangay-clearance', [BarangayClearanceController::class, 'store'])->name('barangay-clearance.store');
    Route::get('/barangay-clearance/{id}', [BarangayClearanceController::class, 'show'])->name('barangay-clearance.show');
    Route::get('/psgc/island-groups/{code}/barangays', [BarangayPermitController::class, 'barangaysByIslandGroup'])->name('psgc.barangays.by-island-group');


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

        Route::get('business-permits', [App\Http\Controllers\Admin\BusinessPermitController::class, 'index'])
            ->name('admin.business-permits');
        Route::get('business-permits/{id}', [App\Http\Controllers\Admin\BusinessPermitController::class, 'show'])
            ->name('admin.business-permits.show');
        Route::delete('business-permits/{id}', [App\Http\Controllers\Admin\BusinessPermitController::class, 'destroy'])
            ->name('admin.business-permits.destroy');
        // View supporting document file inline in browser
        Route::get('business-permits/{id}/documents/{docId}', [App\Http\Controllers\Admin\BusinessPermitController::class, 'viewDocument'])
            ->name('admin.business-permits.documents.view');
        // Update status and remarks
        Route::post('business-permits/{id}/status', [App\Http\Controllers\Admin\BusinessPermitController::class, 'updateStatus'])
            ->name('admin.business-permits.update-status');
    });
});



require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
