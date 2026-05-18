<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_id',
        'chunk_index',
        'chunk_text',
        'embedding',
    ];

    protected $casts = [
        'embedding' => 'array',
    ];

    /**
     * Get the document that owns the chunk.
     */
    public function document()
    {
        return $this->belongsTo(Document::class);
    }
}