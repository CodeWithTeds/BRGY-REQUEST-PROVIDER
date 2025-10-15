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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            // Polymorphic relation to any document request (permit, clearance, certificates)
            $table->morphs('appointable');
            $table->timestamp('appointment_at');
            $table->string('status')->default('scheduled'); // scheduled, completed, cancelled, no_show
            $table->text('remarks')->nullable();
            $table->timestamps();

            // Helpful index for querying by scheduled time
            $table->index('appointment_at', 'appointments_appointment_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};