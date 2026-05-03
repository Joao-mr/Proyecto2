<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Data loading moved to GameImageCatalogSeeder to keep migrations schema-only.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No schema changes to reverse.
    }
};
