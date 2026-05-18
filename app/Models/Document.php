<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'filename',
        'path',
        'status',
    ];

    /**
     * Get the user that owns the document.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the chunks for the document.
     */
    public function chunks()
    {
        return $this->hasMany(DocumentChunk::class);
    }

    /**
     * Get the chat sessions for the document.
     */
    public function chatSessions()
    {
        return $this->hasMany(ChatSession::class);
    }

    /**
     * Get the flashcards for the document.
     */
    public function flashcards()
    {
        return $this->hasMany(Flashcard::class);
    }

    /**
     * Get the quizzes for the document.
     */
    public function quizzes()
    {
        return $this->hasMany(Quiz::class);
    }

    /**
     * Check if document is ready for chat/generation
     */
    public function isProcessed()
    {
        return $this->status === 'processed';
    }
}