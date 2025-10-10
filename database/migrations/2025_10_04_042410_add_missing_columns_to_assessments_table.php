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
        Schema::table('assessments', function (Blueprint $table) {
            // Add total_time column if it doesn't exist
            if (!Schema::hasColumn('assessments', 'total_time')) {
                $table->integer('total_time')->default(30)->after('category');
            }
            
            // Add name column if it doesn't exist (as an alias for title)
            if (!Schema::hasColumn('assessments', 'name')) {
                $table->string('name')->nullable()->after('id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessments', function (Blueprint $table) {
            if (Schema::hasColumn('assessments', 'total_time')) {
                $table->dropColumn('total_time');
            }
            if (Schema::hasColumn('assessments', 'name')) {
                $table->dropColumn('name');
            }
        });
    }
};
