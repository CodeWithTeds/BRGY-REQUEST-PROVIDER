<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('availability_window_id')
                ->nullable()
                ->after('appointment_at')
                ->constrained('availability_windows')
                ->nullOnDelete();
            $table->index(['availability_window_id', 'appointment_at'], 'appointments_window_time_idx');
        });
    }

    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            try { $table->dropIndex('appointments_window_time_idx'); } catch (\Throwable $e) {}
            $table->dropConstrainedForeignId('availability_window_id');
        });
    }
};