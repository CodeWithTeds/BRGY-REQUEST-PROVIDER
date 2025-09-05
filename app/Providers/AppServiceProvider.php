<?php

namespace App\Providers;

use App\Models\Address;
use App\Models\ApplicantProfile;
use App\Models\BarangayClearance;
use App\Models\BarangayPermit;
use App\Models\SupportingDocument;
use App\Repositories\BarangayClearanceRepository;
use App\Repositories\BussinessPermitRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(BussinessPermitRepository::class, function ($app) {
            return new BussinessPermitRepository(
                new BarangayPermit(),
                new ApplicantProfile(),
                new Address(),
                new SupportingDocument()
            );
        });

        $this->app->singleton(BarangayClearanceRepository::class, function ($app) {
            return new BarangayClearanceRepository(
                new BarangayClearance(),
                new ApplicantProfile(),
                new Address(),
                new SupportingDocument()
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
