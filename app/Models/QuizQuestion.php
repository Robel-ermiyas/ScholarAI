<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'quiz_id',
        'question',
        'options',
        'correct_answer',
    ];

    protected $casts = [
        'options' => 'array',
    ];

    /**
     * Get the quiz that owns the question.
     */
    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    /**
     * Get the options as an array with letter keys.
     */
    public function getOptionsWithKeysAttribute()
    {
        $options = $this->options;
        return [
            'A' => $options[0] ?? null,
            'B' => $options[1] ?? null,
            'C' => $options[2] ?? null,
            'D' => $options[3] ?? null,
        ];
    }

    /**
     * Check if the given answer is correct.
     */
    public function isCorrect($answer)
    {
        return strtoupper($answer) === $this->correct_answer;
    }
}