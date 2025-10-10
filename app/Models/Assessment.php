<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Assessment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'title',
        'description',
        'duration',
        'total_marks',
        'pass_percentage',
        'start_date',
        'end_date',
        'status',
        'category',
        'difficulty_level',
        'created_by',
        'allow_multiple_attempts',
        'show_results_immediately',
        'show_correct_answers',
        'time_limit',
        'total_time',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'allow_multiple_attempts' => 'boolean',
        'show_results_immediately' => 'boolean',
        'show_correct_answers' => 'boolean',
        'is_active' => 'boolean'
    ];

    /**
     * Get the admin who created this assessment
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all questions for this assessment
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'assessment_questions');
    }

    /**
     * Get all student attempts for this assessment
     */
    public function studentAssessments(): HasMany
    {
        return $this->hasMany(StudentAssessment::class);
    }

    /**
     * Get all student results for this assessment
     */
    public function studentResults(): HasMany
    {
        return $this->hasMany(StudentResult::class);
    }

    /**
     * Scope for active assessments
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for assessments by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope for assessments by difficulty
     */
    public function scopeByDifficulty($query, $difficulty)
    {
        return $query->where('difficulty_level', $difficulty);
    }

    /**
     * Check if assessment is currently active (within date range)
     */
    public function isCurrentlyActive(): bool
    {
        $now = now();
        
        // If no start or end date, check status only
        if (!$this->start_date && !$this->end_date) {
            return $this->status === 'active';
        }
        
        // Check if current time is within the date range
        $afterStart = !$this->start_date || $now->gte($this->start_date);
        $beforeEnd = !$this->end_date || $now->lte($this->end_date);
        
        return $this->status === 'active' && $afterStart && $beforeEnd;
    }

    /**
     * Get the average score for this assessment
     */
    public function getAverageScoreAttribute()
    {
        try {
            return $this->studentAssessments()->avg('obtained_marks') ?? 0;
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get the pass rate for this assessment
     */
    public function getPassRateAttribute()
    {
        try {
            $totalAttempts = $this->studentAssessments()->count();
            if ($totalAttempts === 0) {
                return 0;
            }
            
            $passedAttempts = $this->studentAssessments()->where('pass_status', 'pass')->count();
            return round(($passedAttempts / $totalAttempts) * 100, 2);
        } catch (\Exception $e) {
            return 0;
        }
    }
    
    /**
     * Get the title attribute (alias for name)
     */
    public function getTitleAttribute()
    {
        return $this->attributes['name'] ?? '';
    }

    /**
     * Set the title attribute (alias for name)
     */
    public function setTitleAttribute($value)
    {
        $this->attributes['name'] = $value;
    }

    /**
     * Get the duration attribute (alias for total_time)
     */
    public function getDurationAttribute()
    {
        return $this->attributes['total_time'] ?? 30;
    }

    /**
     * Set the duration attribute (alias for total_time)
     */
    public function setDurationAttribute($value)
    {
        $this->attributes['total_time'] = $value;
    }

    /**
     * Check if a question can be added to this assessment
     */
    public function canAddQuestion(Question $question): bool
    {
        // Question must be active
        if (!$question->is_active) {
            return false;
        }
        
        // Question category must match assessment category
        if ($question->category !== $this->category) {
            return false;
        }
        
        // Question must not already be assigned to this assessment
        if ($this->questions()->where('question_id', $question->id)->exists()) {
            return false;
        }
        
        return true;
    }
    
    /**
     * Check if assessment is ready for students
     */
    public function isReadyForStudents(): bool
    {
        return $this->is_active && $this->questions()->where('is_active', true)->count() > 0;
    }
}