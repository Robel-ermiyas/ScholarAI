<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'role',
        'content',
    ];

    /**
     * Get the chat session that owns the message.
     */
    public function session()
    {
        return $this->belongsTo(ChatSession::class, 'session_id');
    }

    /**
     * Check if message is from user.
     */
    public function isUser()
    {
        return $this->role === 'user';
    }

    /**
     * Check if message is from assistant.
     */
    public function isAssistant()
    {
        return $this->role === 'assistant';
    }
}