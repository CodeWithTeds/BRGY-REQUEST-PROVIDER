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
            // Change enum to string so we can support 'processing' and future statuses
            $table->string('status')->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangay_permits', function (Blueprint $table) {
            // Revert back to the original enum definition
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending')->change();
        });
    }
};