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
        // Add indexes to barangay_permits
        Schema::table('barangay_permits', function (Blueprint $table) {
            try { $table->index('created_at', 'bp_created_at_idx'); } catch (\Throwable $e) {}
        });

        // Add indexes to barangay_clearances
        Schema::table('barangay_clearances', function (Blueprint $table) {
            try { $table->index('created_at', 'bc_created_at_idx'); } catch (\Throwable $e) {}
        });

        // Add indexes to certificate_of_residencies
        Schema::table('certificate_of_residencies', function (Blueprint $table) {
            try { $table->index('created_at', 'cr_created_at_idx'); } catch (\Throwable $e) {}
        });

        // Add indexes to certificate_of_indigencies
        Schema::table('certificate_of_indigencies', function (Blueprint $table) {
            try { $table->index('status', 'ci_status_idx'); } catch (\Throwable $e) {}
            try { $table->index('application_date', 'ci_application_date_idx'); } catch (\Throwable $e) {}
            try { $table->index('user_id', 'ci_user_id_idx'); } catch (\Throwable $e) {}
            try { $table->index('created_at', 'ci_created_at_idx'); } catch (\Throwable $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangay_permits', function (Blueprint $table) {
            try { $table->dropIndex('bp_created_at_idx'); } catch (\Throwable $e) {}
        });

        Schema::table('barangay_clearances', function (Blueprint $table) {
            try { $table->dropIndex('bc_created_at_idx'); } catch (\Throwable $e) {}
        });

        Schema::table('certificate_of_residencies', function (Blueprint $table) {
            try { $table->dropIndex('cr_created_at_idx'); } catch (\Throwable $e) {}
        });

        Schema::table('certificate_of_indigencies', function (Blueprint $table) {
            try { $table->dropIndex('ci_status_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('ci_application_date_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('ci_user_id_idx'); } catch (\Throwable $e) {}
            try { $table->dropIndex('ci_created_at_idx'); } catch (\Throwable $e) {}
        });
    }
};
