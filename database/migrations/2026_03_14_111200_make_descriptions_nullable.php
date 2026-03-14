<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE expenses MODIFY description VARCHAR(255) NULL');
        DB::statement('ALTER TABLE incomes MODIFY description VARCHAR(255) NULL');
        DB::statement('ALTER TABLE categories MODIFY description VARCHAR(255) NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE expenses SET description = '' WHERE description IS NULL");
        DB::statement("UPDATE incomes SET description = '' WHERE description IS NULL");
        DB::statement("UPDATE categories SET description = '' WHERE description IS NULL");

        DB::statement('ALTER TABLE expenses MODIFY description VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE incomes MODIFY description VARCHAR(255) NOT NULL');
        DB::statement('ALTER TABLE categories MODIFY description VARCHAR(255) NOT NULL');
    }
};
