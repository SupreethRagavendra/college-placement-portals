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
        // Create categories table if it doesn't exist
        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Seed initial categories
        if (DB::table('categories')->count() === 0) {
            DB::table('categories')->insert([
                ['name' => 'Aptitude', 'description' => 'Logical reasoning and mathematical skills', 'is_active' => true],
                ['name' => 'Technical', 'description' => 'Technical knowledge and skills', 'is_active' => true]
            ]);
        }

        // Update questions table to use category_id
        Schema::table('questions', function (Blueprint $table) {
            // Drop the existing category column if it exists
            if (Schema::hasColumn('questions', 'category')) {
                $table->dropColumn('category');
            }

            // Add category_id column if it doesn't exist
            if (!Schema::hasColumn('questions', 'category_id')) {
                // Get the default Aptitude category ID
                $aptitudeCategoryId = DB::table('categories')->where('name', 'Aptitude')->value('id');
                
                $table->unsignedBigInteger('category_id')
                    ->default($aptitudeCategoryId)
                    ->nullable(false)
                    ->change();
                
                $table->foreign('category_id')
                    ->references('id')
                    ->on('categories')
                    ->onDelete('restrict');
            }
        });

        // Ensure all existing questions have a category_id
        $categories = DB::table('categories')->pluck('id', 'name');
        DB::table('questions')
            ->whereNull('category_id')
            ->update([
                'category_id' => $categories['Aptitude']
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');
            $table->enum('category', ['Aptitude', 'Technical'])->nullable();
        });

        Schema::dropIfExists('categories');
    }
};
