<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Convert enum document_type to a VARCHAR to allow new types
        DB::statement('ALTER TABLE `supporting_documents` MODIFY `document_type` VARCHAR(100) NOT NULL');
    }

    public function down(): void
    {
        // If you need to revert, fallback to a conservative enum set
        DB::statement("ALTER TABLE `supporting_documents` MODIFY `document_type` ENUM('certificate_of_residency','lease_contract','utility_bill') NOT NULL");
    }
};