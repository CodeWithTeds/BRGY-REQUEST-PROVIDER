<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clerks', function (Blueprint $table) {
            // Replace unsignedBigInteger + manual foreign with fluent foreignId
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('clerks', function (Blueprint $table) {
            // Drop the constrained foreign id in one call
            $table->dropConstrainedForeignId('user_id');
        });
    }
};