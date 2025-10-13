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
        Schema::table('supporting_documents', function (Blueprint $table) {
            if (!Schema::hasColumn('supporting_documents', 'certificate_of_residency_id')) {
                $table->foreignId('certificate_of_residency_id')
                    ->nullable()
                    ->constrained('certificate_of_residencies')
                    ->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supporting_documents', function (Blueprint $table) {
            try { $table->dropForeign(['certificate_of_residency_id']); } catch (\Throwable $e) {}
            if (Schema::hasColumn('supporting_documents', 'certificate_of_residency_id')) {
                $table->dropColumn('certificate_of_residency_id');
            }
        });
    }
};