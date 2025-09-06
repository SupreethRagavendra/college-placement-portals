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
        if (!Schema::hasTable('questions')) {
            Schema::create('questions', function (Blueprint $table) {
                $table->id();
                $table->text('question_text');
                $table->json('options'); // Store 4 options as JSON array
                $table->integer('correct_option'); // 0-3 index for correct answer
                $table->enum('category', ['Aptitude', 'Technical']);
                $table->enum('difficulty', ['Easy', 'Medium', 'Hard'])->default('Medium');
                $table->integer('time_per_question')->default(60); // seconds
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                
                // Indexes for better performance
                $table->index('category');
                $table->index('is_active');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};