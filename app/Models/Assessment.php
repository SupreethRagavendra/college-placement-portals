<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'category',
        'time_limit',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'time_limit' => 'integer'
    ];

    /**
     * Questions associated with this assessment
     */
    public function questions(): BelongsToMany
    {
        return $this->belongsToMany(Question::class, 'assessment_questions');
    }

    /**
     * Student results for this assessment
     */
    public function studentResults(): HasMany
    {
        return $this->hasMany(StudentResult::class);
    }

    /**
     * Get average score for this assessment
     */
    public function getAverageScoreAttribute(): float
    {
        return round($this->studentResults()->avg('score') ?? 0, 2);
    }

    /**
     * Get average percentage for this assessment
     */
    public function getAveragePercentageAttribute(): float
    {
        $results = $this->studentResults()->get();
        if ($results->isEmpty()) return 0;

        $totalPercentage = $results->sum(function($result) {
            return $result->total_questions > 0 ? ($result->score / $result->total_questions) * 100 : 0;
        });

        return round($totalPercentage / $results->count(), 2);
    }

    /**
     * Get total attempts count
     */
    public function getAttemptCountAttribute(): int
    {
        return $this->studentResults()->count();
    }

    /**
     * Get highest score for this assessment
     */
    public function getHighestScoreAttribute(): int
    {
        return $this->studentResults()->max('score') ?? 0;
    }

    /**
     * Get lowest score for this assessment
     */
    public function getLowestScoreAttribute(): int
    {
        return $this->studentResults()->min('score') ?? 0;
    }

    /**
     * Scope for active assessments
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
     * Accessor for title (maps to name)
     */
    public function getTitleAttribute()
    {
        return $this->name;
    }

    /**
     * Mutator for title (maps to name)
     */
    public function setTitleAttribute($value)
    {
        $this->attributes['name'] = $value;
    }

    /**
     * Accessor for total_time (maps to time_limit)
     */
    public function getTotalTimeAttribute()
    {
        return $this->time_limit;
    }

    /**
     * Mutator for total_time (maps to time_limit)
     */
    public function setTotalTimeAttribute($value)
    {
        $this->attributes['time_limit'] = $value;
    }
}