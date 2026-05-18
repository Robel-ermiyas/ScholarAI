<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Quiz extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'title',
    ];

    /**
     * Get the document that owns the quiz.
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user that owns the quiz.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all questions for this quiz.
     */
    public function questions()
    {
        return $this->hasMany(QuizQuestion::class);
    }

    /**
     * Get all attempts for this quiz.
     */
    public function attempts()
    {
        return $this->hasMany(QuizAttempt::class);
    }

    /**
     * Get the user's latest attempt for this quiz.
     */
    public function latestAttempt()
    {
        return $this->hasOne(QuizAttempt::class)->latest();
    }

    /**
     * Get the user's best score for this quiz.
     */
    public function bestScore()
    {
        return $this->hasOne(QuizAttempt::class)
                    ->selectRaw('quiz_id, MAX(score) as max_score')
                    ->groupBy('quiz_id');
    }
}