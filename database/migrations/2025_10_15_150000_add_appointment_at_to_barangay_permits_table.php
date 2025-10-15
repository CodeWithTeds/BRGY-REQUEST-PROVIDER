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
        Schema::table('barangay_permits', function (Blueprint $table) {
            $table->timestamp('appointment_at')->nullable()->after('remarks');
            $table->index('appointment_at', 'bp_appointment_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangay_permits', function (Blueprint $table) {
            try { $table->dropIndex('bp_appointment_at_idx'); } catch (\Throwable $e) {}
            $table->dropColumn('appointment_at');
        });
    }
};