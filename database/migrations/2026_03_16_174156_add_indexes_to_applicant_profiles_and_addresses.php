<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            try { $table->index(['last_name', 'first_name']); } catch (\Throwable $e) {}
            try { $table->index('barangay_permit_id'); } catch (\Throwable $e) {}
        });

        Schema::table('addresses', function (Blueprint $table) {
            try { $table->index('barangay_permit_id'); } catch (\Throwable $e) {}
            try { $table->index('barangay_id'); } catch (\Throwable $e) {}
        });

        Schema::table('supporting_documents', function (Blueprint $table) {
            try { $table->index('barangay_permit_id'); } catch (\Throwable $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            try { $table->dropIndex(['last_name', 'first_name']); } catch (\Throwable $e) {}
            try { $table->dropIndex(['barangay_permit_id']); } catch (\Throwable $e) {}
        });

        Schema::table('addresses', function (Blueprint $table) {
            try { $table->dropIndex(['barangay_permit_id']); } catch (\Throwable $e) {}
            try { $table->dropIndex(['barangay_id']); } catch (\Throwable $e) {}
        });

        Schema::table('supporting_documents', function (Blueprint $table) {
            try { $table->dropIndex(['barangay_permit_id']); } catch (\Throwable $e) {}
        });
    }
};
