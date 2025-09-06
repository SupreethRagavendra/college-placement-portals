<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Question extends Model
{
    use HasFactory;

    public $timestamps = false; // Disable timestamps since the existing table doesn't have them

    protected $fillable = [
        'question',
        'options',
        'correct_option',
        'category_id',
        'difficulty',
        'time_per_question',
        'is_active'
    ];

    protected $casts = [
        'options' => 'array',
        'correct_option' => 'integer',
        'category_id' => 'integer',
        'time_per_question' => 'integer',
        'is_active' => 'boolean'
    ];

    /**
     * Assessments that use this question
     */
    public function assessments(): BelongsToMany
    {
        return $this->belongsToMany(Assessment::class, 'assessment_questions');
    }

    /**
     * Get the correct answer text
     */
    public function getCorrectAnswerAttribute(): ?string
    {
        if (!is_array($this->options) || !isset($this->options[$this->correct_option])) {
            return null;
        }
        return $this->options[$this->correct_option];
    }

    /**
     * Get formatted options with letters (A, B, C, D)
     */
    public function getFormattedOptionsAttribute(): array
    {
        if (!is_array($this->options)) {
            return [];
        }

        $letters = ['A', 'B', 'C', 'D'];
        $formatted = [];

        foreach ($this->options as $index => $option) {
            if (isset($letters[$index])) {
                $formatted[$letters[$index]] = $option;
            }
        }

        return $formatted;
    }

    /**
     * Get correct option letter
     */
    public function getCorrectOptionLetterAttribute(): string
    {
        $letters = ['A', 'B', 'C', 'D'];
        return $letters[$this->correct_option] ?? 'A';
    }

    /**
     * Check if given answer is correct
     */
    public function isCorrectAnswer(int $answerIndex): bool
    {
        return $this->correct_option === $answerIndex;
    }

    /**
     * Scope for active questions
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for specific category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for specific difficulty
     */
    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    /**
     * Accessor for question_text (maps to question)
     */
    public function getQuestionTextAttribute()
    {
        return $this->question;
    }

    /**
     * Mutator for question_text (maps to question)
     */
    public function setQuestionTextAttribute($value)
    {
        $this->attributes['question'] = $value;
    }

    /**
     * Accessor for category (maps from category_id)
     */
    public function getCategoryAttribute()
    {
        $categoryMap = [1 => 'Aptitude', 2 => 'Technical'];
        return $categoryMap[$this->category_id] ?? 'Unknown';
    }

    /**
     * Mutator for category (maps to category_id)
     */
    public function setCategoryAttribute($value)
    {
        $categoryMap = ['Aptitude' => 1, 'Technical' => 2];
        $this->attributes['category_id'] = $categoryMap[$value] ?? 1;
    }
}