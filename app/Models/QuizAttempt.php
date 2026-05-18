<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'user_id',
        'score',
        'total',
    ];

    /**
     * Get the quiz that was attempted.
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the user who made the attempt.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate the percentage score.
     */
    public function getPercentageAttribute()
    {
        if ($this->total === 0) {
            return 0;
        }
        return round(($this->score / $this->total) * 100, 2);
    }

    /**
     * Check if the user passed (60% or higher).
     */
    public function getPassedAttribute()
    {
        return $this->percentage >= 60;
    }
}