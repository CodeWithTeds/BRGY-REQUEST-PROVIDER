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
        Schema::table('users', function (Blueprint $table) {
            $table->index('name', 'users_name_index');
        });

        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->index('first_name', 'ap_first_name_idx');
            $table->index('last_name', 'ap_last_name_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_name_index');
        });

        Schema::table('applicant_profiles', function (Blueprint $table) {
            $table->dropIndex('ap_first_name_idx');
            $table->dropIndex('ap_last_name_idx');
        });
    }
};
