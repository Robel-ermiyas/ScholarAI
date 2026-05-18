<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'document_id',
        'title',
    ];

    /**
     * Get the user that owns the session.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the document being discussed in this session.
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get all messages in this session.
     */
    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'session_id');
    }

    /**
     * Get the latest message in the session.
     */
    public function latestMessage()
    {
        return $this->hasOne(ChatMessage::class, 'session_id')->latest();
    }
}