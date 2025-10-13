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
        // Business Permits
        Schema::table('barangay_permits', function (Blueprint $table) {
            try { $table->index('status', 'bp_status_idx'); } catch (\Throwable $e) {}
            try { $table->index('application_date', 'bp_application_date_idx'); } catch (\Throwable $e) {}
            try { $table->index('user_id', 'bp_user_id_idx'); } catch (\Throwable $e) {}
        });

        // Barangay Clearances
        Schema::table('barangay_clearances', function (Blueprint $table) {
            try { $table->index('status', 'bc_status_idx'); } catch (\Throwable $e) {}
            try { $table->index('application_date', 'bc_application_date_idx'); } catch (\Throwable $e) {}
            try { $table->index('user_id', 'bc_user_id_idx'); } catch (\Throwable $e) {}
        });

        // Certificate of Residency
        Schema::table('certificate_of_residencies', function (Blueprint $table) {
            try { $table->index('status', 'cr_status_idx'); } catch (\Throwable $e) {}
            try { $table->index('application_date', 'cr_application_date_idx'); } catch (\Throwable $e) {}
            try { $table->index('user_id', 'cr_user_id_idx'); } catch (\Throwable $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangay_permits', function (Blueprint $table) {
            try { $table->dropIndex('bp_status_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('bp_application_date_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('bp_user_id_idx'); } catch (\Throwable $e) {}
        });

        Schema::table('barangay_clearances', function (Blueprint $table) {
            try { $table->dropIndex('bc_status_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('bc_application_date_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('bc_user_id_idx'); } catch (\Throwable $e) {}
        });

        Schema::table('certificate_of_residencies', function (Blueprint $table) {
            try { $table->dropIndex('cr_status_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('cr_application_date_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('cr_user_id_idx'); } catch (\Throwable $e) {}
        });
    }
};