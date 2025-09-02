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
            $table->foreignId('barangay_clearance_id')->nullable()->constrained('barangay_clearances')->onDelete('cascade');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->foreignId('barangay_clearance_id')->nullable()->constrained('barangay_clearances')->onDelete('cascade');
        });

        Schema::table('supporting_documents', function (Blueprint $table) {
            $table->foreignId('barangay_clearance_id')->nullable()->constrained('barangay_clearances')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->dropForeign(['barangay_clearance_id']);
            $table->dropColumn('barangay_clearance_id');
        });

        Schema::table('addresses', function (Blueprint $table) {
            $table->dropForeign(['barangay_clearance_id']);
            $table->dropColumn('barangay_clearance_id');
        });

        Schema::table('supporting_documents', function (Blueprint $table) {
            $table->dropForeign(['barangay_clearance_id']);
            $table->dropColumn('barangay_clearance_id');
        });
    }
};