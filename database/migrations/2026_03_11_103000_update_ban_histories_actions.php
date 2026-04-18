<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For SQLite, we might need to handle enum changes differently if it's strictly enforced via CHECK constraints
        // However, Laravel's Schema builder usually handles this if we re-declare the column.
        // Given the error specifically mentions the CHECK constraint, we'll try to update the column definition.
        
        Schema::table('ban_histories', function (Blueprint $table) {
            // Note: Enum changes in SQLite are tricky. Laravel often creates a new table and copies data.
            // We'll broaden the allowed actions.
            $table->enum('action', ['banned', 'restored', 'activated', 'deactivated'])->default('banned')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ban_histories', function (Blueprint $table) {
            $table->enum('action', ['banned', 'restored'])->default('banned')->change();
        });
    }
};
