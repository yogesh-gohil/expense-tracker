<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->string('description', 255)->nullable()->change();
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->string('description', 255)->nullable()->change();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('description', 255)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("UPDATE expenses SET description = '' WHERE description IS NULL");
        DB::statement("UPDATE incomes SET description = '' WHERE description IS NULL");
        DB::statement("UPDATE categories SET description = '' WHERE description IS NULL");

        Schema::table('expenses', function (Blueprint $table) {
            $table->string('description', 255)->nullable(false)->change();
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->string('description', 255)->nullable(false)->change();
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->string('description', 255)->nullable(false)->change();
        });
    }
};
