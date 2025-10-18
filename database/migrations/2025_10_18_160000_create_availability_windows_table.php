<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('availability_windows', function (Blueprint $table) {
            $table->id();
            // Optional: clerk/office-specific availability in the future
            $table->foreignId('clerk_id')->nullable()->constrained('clerks')->nullOnDelete();
            // Local date for which this window applies (Asia/Manila implied)
            $table->date('date');
            // Window bounds in local time
            $table->time('start_time');
            $table->time('end_time');
            // Slot granularity in minutes (e.g., 30)
            $table->unsignedSmallInteger('slot_interval_minutes')->default(30);
            // Capacity per time slot (e.g., 10)
            $table->unsignedSmallInteger('capacity_per_slot')->default(10);
            // Window active flag
            $table->boolean('is_active')->default(true);
            $table->string('remarks')->nullable();
            $table->timestamps();

            $table->index(['date', 'clerk_id']);
            $table->index(['date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('availability_windows');
    }
};