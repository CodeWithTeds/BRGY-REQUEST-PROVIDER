<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('addresses', 'city_code')) {
                $table->string('city_code')->nullable()->index();
            }
            if (!Schema::hasColumn('addresses', 'province_code')) {
                $table->string('province_code')->nullable()->index();
            }
            if (!Schema::hasColumn('addresses', 'region_code')) {
                $table->string('region_code')->nullable()->index();
            }
        });

        // Backfill from existing barangay_id using PSGC barangays table
        $psgcBarangaysTable = 'psgc_barangays';
        $citiesTable = config('psgc.tables.cities', 'cities');
        $provincesTable = config('psgc.tables.provinces', 'provinces');
        $regionsTable = config('psgc.tables.regions', 'regions');

        try {
            DB::statement("UPDATE addresses a
                LEFT JOIN {$psgcBarangaysTable} b ON b.id = a.barangay_id
                SET a.city_code = b.city_code,
                    a.province_code = b.province_code,
                    a.region_code = b.region_code
            ");
        } catch (\Throwable $e) {
            // ignore if tables/columns absent
        }

        // Add foreign key constraints when possible
        Schema::table('addresses', function (Blueprint $table) use ($citiesTable, $provincesTable, $regionsTable) {
            try {
                $table->foreign('city_code')->references('code')->on($citiesTable)->cascadeOnUpdate()->restrictOnDelete();
            } catch (\Throwable $e) {
            }
            try {
                $table->foreign('province_code')->references('code')->on($provincesTable)->cascadeOnUpdate()->restrictOnDelete();
            } catch (\Throwable $e) {
            }
            try {
                $table->foreign('region_code')->references('code')->on($regionsTable)->cascadeOnUpdate()->restrictOnDelete();
            } catch (\Throwable $e) {
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            try {
                $table->dropForeign(['city_code']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['province_code']);
            } catch (\Throwable $e) {
            }
            try {
                $table->dropForeign(['region_code']);
            } catch (\Throwable $e) {
            }
        });

        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'city_code')) {
                $table->dropColumn('city_code');
            }
            if (Schema::hasColumn('addresses', 'province_code')) {
                $table->dropColumn('province_code');
            }
            if (Schema::hasColumn('addresses', 'region_code')) {
                $table->dropColumn('region_code');
            }
        });
    }
};
