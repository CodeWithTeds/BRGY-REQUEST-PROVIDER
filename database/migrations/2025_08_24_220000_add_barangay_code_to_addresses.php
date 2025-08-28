<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            if (!Schema::hasColumn('addresses', 'barangay_code')) {
                $table->string('barangay_code')->nullable()->index();
            }
        });

        try {
            DB::statement("UPDATE addresses a\n LEFT JOIN psgc_barangays b ON b.id = a.barangay_id\n                SET a.barangay_code = b.code");
        } catch (\Throwable $e) {
            // ignore if tables/columns absent
        }

        Schema::table('addresses', function (Blueprint $table) {
            try {
                $table->foreign('barangay_code')->references('code')->on('barangays')->cascadeOnUpdate()->restrictOnDelete();
            } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            try { $table->dropForeign(['barangay_code']); } catch (\Throwable $e) {}
        });
        Schema::table('addresses', function (Blueprint $table) {
            if (Schema::hasColumn('addresses', 'barangay_code')) {
                $table->dropColumn('barangay_code');
            }
        });
    }
};