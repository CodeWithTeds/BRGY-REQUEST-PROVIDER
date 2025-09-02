<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop FK if exists, make column nullable via raw SQL (avoids requiring doctrine/dbal), then re-add FK
        Schema::table('addresses', function (Blueprint $table) {
            try { $table->dropForeign(['barangay_id']); } catch (\Throwable $e) {}
        });

        try {
            DB::statement('ALTER TABLE addresses MODIFY barangay_id BIGINT UNSIGNED NULL');
        } catch (\Throwable $e) {
            // If ALTER fails (e.g., different driver), ignore to avoid blocking other migrations
        }

        Schema::table('addresses', function (Blueprint $table) {
            try { $table->foreign('barangay_id')->references('id')->on('psgc_barangays'); } catch (\Throwable $e) {}
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            try { $table->dropForeign(['barangay_id']); } catch (\Throwable $e) {}
        });

        try {
            DB::statement('ALTER TABLE addresses MODIFY barangay_id BIGINT UNSIGNED NOT NULL');
        } catch (\Throwable $e) {
            // ignore
        }

        Schema::table('addresses', function (Blueprint $table) {
            try { $table->foreign('barangay_id')->references('id')->on('psgc_barangays'); } catch (\Throwable $e) {}
        });
    }
};