<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Resident\BarangayPermitController;
use App\Http\Controllers\Resident\BarangayClearanceController;
use App\Http\Controllers\Resident\CertificateOfResidencyController;
 use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');



Route::prefix('resident')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\Resident\DashboardController::class, 'index'])
        ->name('resident.dashboard');

    Route::get('/barangay-business-permit', function () {
        return Inertia::render('Resident/BarangayBusinessPermit');
    })->name('resident.barangay-business-permit');

    Route::get('/barangay-clearance', function () {
        return Inertia::render('Resident/BarangayClearance');
    })->name('resident.barangay-clearance');

    Route::get('certificate-of-residency', [CertificateOfResidencyController::class, 'create'])->name('resident.certificate-of-residency.create');
    Route::post('certificate-of-residency', [CertificateOfResidencyController::class, 'store'])->name('resident.certificate-of-residency.store');

    // Appointment scheduling for approved residency certificates
    Route::get('certificate-of-residency/schedule', [CertificateOfResidencyController::class, 'schedule'])
        ->name('resident.certificate-of-residency.schedule');
    Route::post('certificate-of-residency/schedule', [CertificateOfResidencyController::class, 'scheduleStore'])
        ->name('resident.certificate-of-residency.schedule.store');
    // Availability for occupied appointment times (residency)
    Route::get('certificate-of-residency/availability', [CertificateOfResidencyController::class, 'availability'])
        ->name('resident.certificate-of-residency.availability');

    // Resident: Download approved residency certificate as PDF
    Route::get('certificate-of-residency/{id}/pdf', [CertificateOfResidencyController::class, 'downloadPdf'])
        ->whereNumber('id')
        ->name('resident.certificate-of-residency.pdf');

    // Certificate of Indigency (Resident)
    Route::get('certificate-of-indigency', [App\Http\Controllers\Resident\CertificateOfIndigencyController::class, 'create'])->name('resident.certificate-of-indigency.create');
    Route::post('certificate-of-indigency', [App\Http\Controllers\Resident\CertificateOfIndigencyController::class, 'store'])->name('resident.certificate-of-indigency.store');

    // Appointment scheduling for approved indigency certificates
    Route::get('certificate-of-indigency/schedule', [App\Http\Controllers\Resident\CertificateOfIndigencyController::class, 'schedule'])
        ->name('resident.certificate-of-indigency.schedule');
    Route::post('certificate-of-indigency/schedule', [App\Http\Controllers\Resident\CertificateOfIndigencyController::class, 'scheduleStore'])
        ->name('resident.certificate-of-indigency.schedule.store');
    // Availability for occupied appointment times (indigency)
    Route::get('certificate-of-indigency/availability', [App\Http\Controllers\Resident\CertificateOfIndigencyController::class, 'availability'])
        ->name('resident.certificate-of-indigency.availability');

    // Resident: Download approved indigency certificate as PDF
    Route::get('certificate-of-indigency/{id}/pdf', [App\Http\Controllers\Resident\CertificateOfIndigencyController::class, 'downloadPdf'])
        ->whereNumber('id')
        ->name('resident.certificate-of-indigency.pdf');

    Route::get('/barangay-permit/create', [BarangayPermitController::class, 'create'])->name('barangay-permit.create');
    Route::post('/barangay-permit', [BarangayPermitController::class, 'store'])->name('barangay-permit.store');
    // Resident: Download approved permit as PDF
    Route::get('/barangay-permit/{id}/pdf', [BarangayPermitController::class, 'downloadPdf'])
        ->name('barangay-permit.pdf');

    // Appointment scheduling for approved permits
    Route::get('/barangay-permit/schedule', [BarangayPermitController::class, 'schedule'])
        ->name('barangay-permit.schedule');
    Route::post('/barangay-permit/schedule', [BarangayPermitController::class, 'scheduleStore'])
        ->name('barangay-permit.schedule.store');
    // Availability for occupied appointment times (permit)
    Route::get('/barangay-permit/availability', [BarangayPermitController::class, 'availability'])
        ->name('barangay-permit.availability');

    Route::get('/barangay-clearance/create', [BarangayClearanceController::class, 'create'])->name('barangay-clearance.create');
    Route::post('/barangay-clearance', [BarangayClearanceController::class, 'store'])->name('barangay-clearance.store');

    // Appointment scheduling for approved clearances (define BEFORE dynamic {id} routes)
    Route::get('/barangay-clearance/schedule', [BarangayClearanceController::class, 'schedule'])
        ->name('barangay-clearance.schedule');
    Route::post('/barangay-clearance/schedule', [BarangayClearanceController::class, 'scheduleStore'])
        ->name('barangay-clearance.schedule.store');
    // Availability for occupied appointment times (clearance)
    Route::get('/barangay-clearance/availability', [BarangayClearanceController::class, 'availability'])
        ->name('barangay-clearance.availability');

    // Resident: Show and download (id must be numeric)
    Route::get('/barangay-clearance/{id}', [BarangayClearanceController::class, 'show'])
        ->whereNumber('id')
        ->name('barangay-clearance.show');
    Route::get('/barangay-clearance/{id}/pdf', [BarangayClearanceController::class, 'downloadPdf'])
        ->whereNumber('id')
        ->name('barangay-clearance.pdf');
    Route::get('/psgc/island-groups/{code}/barangays', [BarangayPermitController::class, 'barangaysByIslandGroup'])->name('psgc.barangays.by-island-group');


    // New PSGC endpoints
    Route::get('/psgc/regions', [BarangayPermitController::class, 'regions'])->name('psgc.regions');
    Route::get('/psgc/regions/{code}/provinces', [BarangayPermitController::class, 'provincesByRegion'])->name('psgc.provinces.by-region');
    Route::get('/psgc/provinces/{code}/cities', [BarangayPermitController::class, 'citiesByProvince'])->name('psgc.cities.by-province');
    Route::get('/psgc/regions/{code}/cities', [BarangayPermitController::class, 'citiesByRegion'])->name('psgc.cities.by-region');
    Route::get('/psgc/cities/{code}/barangays', [BarangayPermitController::class, 'barangaysByCity'])->name('psgc.barangays.by-city');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if ($user instanceof \App\Models\User && $user->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }
        if ($user instanceof \App\Models\User && $user->isStaff()) {
            return redirect()->route('staff.dashboard');
        }
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

        // Admin Business Permits (controller-backed Inertia pages)
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

        // Admin Barangay Clearances (controller-backed Inertia pages)
        Route::get('barangay-clearances', [App\Http\Controllers\Admin\BarangayClearanceController::class, 'index'])
            ->name('admin.barangay-clearances');
        Route::get('barangay-clearances/{id}', [App\Http\Controllers\Admin\BarangayClearanceController::class, 'show'])
            ->name('admin.barangay-clearances.show');

        // JSON API routes for Barangay Clearances
        Route::get('api/barangay-clearances', [App\Http\Controllers\Admin\BarangayClearanceController::class, 'index'])
            ->name('admin.api.barangay-clearances');

        Route::get('api/barangay-clearances/{id}', [App\Http\Controllers\Admin\BarangayClearanceController::class, 'show'])
            ->name('admin.api.barangay-clearances.show');

        Route::delete('barangay-clearances/{id}', [App\Http\Controllers\Admin\BarangayClearanceController::class, 'destroy'])
            ->name('admin.barangay-clearances.destroy');

        Route::get('barangay-clearances/{id}/documents/{docId}', [App\Http\Controllers\Admin\BarangayClearanceController::class, 'viewDocument'])
            ->name('admin.barangay-clearances.documents.view');
            
        Route::post('barangay-clearances/{id}/status', [App\Http\Controllers\Admin\BarangayClearanceController::class, 'updateStatus'])
            ->name('admin.barangay-clearances.update-status');

        // Admin Residency Certificates (real data)
        Route::get('residency-certificates', [App\Http\Controllers\Admin\ResidencyCertificateController::class, 'index'])
            ->name('admin.residency-certificates');
        Route::get('residency-certificates/{id}', [App\Http\Controllers\Admin\ResidencyCertificateController::class, 'show'])
            ->name('admin.residency-certificates.show');
        Route::delete('residency-certificates/{id}', [App\Http\Controllers\Admin\ResidencyCertificateController::class, 'destroy'])
            ->name('admin.residency-certificates.destroy');
        // View supporting document file inline in browser
        Route::get('residency-certificates/{id}/documents/{docId}', [App\Http\Controllers\Admin\ResidencyCertificateController::class, 'viewDocument'])
            ->name('admin.residency-certificates.documents.view');
        // Update status and remarks
        Route::post('residency-certificates/{id}/status', [App\Http\Controllers\Admin\ResidencyCertificateController::class, 'updateStatus'])
            ->name('admin.residency-certificates.update-status');

        // Admin Indigency Certificates
        Route::get('indigency-certificates', [App\Http\Controllers\Admin\IndigencyCertificateController::class, 'index'])
            ->name('admin.indigency-certificates');
        Route::get('indigency-certificates/{id}', [App\Http\Controllers\Admin\IndigencyCertificateController::class, 'show'])
            ->name('admin.indigency-certificates.show');
        Route::delete('indigency-certificates/{id}', [App\Http\Controllers\Admin\IndigencyCertificateController::class, 'destroy'])
            ->name('admin.indigency-certificates.destroy');
        Route::get('indigency-certificates/{id}/documents/{docId}', [App\Http\Controllers\Admin\IndigencyCertificateController::class, 'viewDocument'])
            ->name('admin.indigency-certificates.documents.view');
        Route::post('indigency-certificates/{id}/status', [App\Http\Controllers\Admin\IndigencyCertificateController::class, 'updateStatus'])
            ->name('admin.indigency-certificates.update-status');

        // Admin Clerks (CRUD)
        Route::get('clerks', [App\Http\Controllers\Admin\ClerkController::class, 'index'])
            ->name('admin.clerks');
        Route::post('clerks', [App\Http\Controllers\Admin\ClerkController::class, 'store'])
            ->name('admin.clerks.store');
        Route::patch('clerks/{id}', [App\Http\Controllers\Admin\ClerkController::class, 'update'])
            ->name('admin.clerks.update');
        Route::delete('clerks/{id}', [App\Http\Controllers\Admin\ClerkController::class, 'destroy'])
            ->name('admin.clerks.destroy');
    });
});



Route::prefix('staff')->middleware(['auth', 'staff'])->group(function () {
    Route::get('dashboard', [App\Http\Controllers\Staff\DashboardController::class, 'index'])
        ->name('staff.dashboard');

    // Staff Business Permits
    Route::get('business-permits', [App\Http\Controllers\Staff\BusinessPermitController::class, 'index'])
        ->name('staff.business-permits');
    Route::get('business-permits/{id}', [App\Http\Controllers\Staff\BusinessPermitController::class, 'show'])
        ->name('staff.business-permits.show');
    Route::post('business-permits/{id}/status', [App\Http\Controllers\Staff\BusinessPermitController::class, 'updateStatus'])
        ->name('staff.business-permits.update-status');
    Route::get('business-permits/{id}/documents/{docId}', [App\Http\Controllers\Staff\BusinessPermitController::class, 'viewDocument'])
        ->name('staff.business-permits.documents.view');

    // Staff Barangay Clearances
    Route::get('barangay-clearances', [App\Http\Controllers\Staff\BarangayClearanceController::class, 'index'])
        ->name('staff.barangay-clearances');
    Route::get('barangay-clearances/{id}', [App\Http\Controllers\Staff\BarangayClearanceController::class, 'show'])
        ->name('staff.barangay-clearances.show');
    Route::post('barangay-clearances/{id}/status', [App\Http\Controllers\Staff\BarangayClearanceController::class, 'updateStatus'])
        ->name('staff.barangay-clearances.update-status');
    Route::get('barangay-clearances/{id}/documents/{docId}', [App\Http\Controllers\Staff\BarangayClearanceController::class, 'viewDocument'])
        ->name('staff.barangay-clearances.documents.view');

    // Staff Residency Certificates
    Route::get('residency-certificates', [App\Http\Controllers\Staff\ResidencyCertificateController::class, 'index'])
        ->name('staff.residency-certificates');
    Route::get('residency-certificates/{id}', [App\Http\Controllers\Staff\ResidencyCertificateController::class, 'show'])
        ->name('staff.residency-certificates.show');
    Route::post('residency-certificates/{id}/status', [App\Http\Controllers\Staff\ResidencyCertificateController::class, 'updateStatus'])
        ->name('staff.residency-certificates.update-status');
    Route::get('residency-certificates/{id}/documents/{docId}', [App\Http\Controllers\Staff\ResidencyCertificateController::class, 'viewDocument'])
        ->name('staff.residency-certificates.documents.view');

    // Staff Indigency Certificates
    Route::get('indigency-certificates', [App\Http\Controllers\Staff\IndigencyCertificateController::class, 'index'])
        ->name('staff.indigency-certificates');
    Route::get('indigency-certificates/{id}', [App\Http\Controllers\Staff\IndigencyCertificateController::class, 'show'])
        ->name('staff.indigency-certificates.show');
    Route::post('indigency-certificates/{id}/status', [App\Http\Controllers\Staff\IndigencyCertificateController::class, 'updateStatus'])
        ->name('staff.indigency-certificates.update-status');
    Route::get('indigency-certificates/{id}/documents/{docId}', [App\Http\Controllers\Staff\IndigencyCertificateController::class, 'viewDocument'])
        ->name('staff.indigency-certificates.documents.view');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
