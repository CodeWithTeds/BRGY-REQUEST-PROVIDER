<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('supporting_documents', function (Blueprint $table) {
            $table->foreignId('certificate_of_indigency_id')
                ->nullable()
                ->after('certificate_of_residency_id')
                ->constrained('certificate_of_indigencies')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('supporting_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('certificate_of_indigency_id');
        });
    }
};