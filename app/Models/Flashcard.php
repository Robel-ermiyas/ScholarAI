<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flashcard extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'user_id',
        'question',
        'answer',
    ];

    /**
     * Get the document that owns the flashcard.
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }

    /**
     * Get the user that owns the flashcard.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}