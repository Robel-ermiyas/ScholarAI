<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\DocumentChunk;
use App\Services\GeminiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Smalot\PdfParser\Parser;

class ProcessDocumentJob implements ShouldQueue
{
    use Queueable;

    /**
     * The document instance to process.
     */
    protected Document $document;

    /**
     * Number of times to retry the job.
     */
    public int $tries = 3;

    /**
     * Timeout in seconds.
     */
    public int $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(Document $document)
    {
        $this->document = $document;
    }

    /**
     * Execute the job.
     */
    public function handle(GeminiService $gemini): void
    {
        try {
            // Update status to processing
            $this->document->update(['status' => 'processing']);

            // Get the full path to the PDF file
            $filePath = Storage::disk('private')->path($this->document->path);

            if (!file_exists($filePath)) {
                throw new \Exception("PDF file not found at: {$filePath}");
            }

            // Extract text from PDF
            $parser = new Parser();
            $pdf = $parser->parseFile($filePath);
            $fullText = $pdf->getText();

            if (empty(trim($fullText))) {
                throw new \Exception("No text could be extracted from the PDF");
            }

            // Split text into chunks (approximately 500 words each)
            $chunks = $this->splitTextIntoChunks($fullText, 500);

            if (empty($chunks)) {
                throw new \Exception("No text chunks generated from the document");
            }

            // Process each chunk
            foreach ($chunks as $index => $chunkText) {
                // Create chunk record first
                $chunk = DocumentChunk::create([
                    'document_id' => $this->document->id,
                    'chunk_index' => $index,
                    'chunk_text' => $chunkText,
                    'embedding' => null,
                ]);

                // Generate embedding for the chunk
                $embedding = $gemini->embed($chunkText);

                // Update chunk with embedding
                $chunk->update([
                    'embedding' => $embedding,
                ]);

                // Log progress
                Log::info("Processed chunk {$index} for document {$this->document->id}");
            }

            // Update status to processed
            $this->document->update(['status' => 'processed']);

            Log::info("Document {$this->document->id} processed successfully");

        } catch (\Exception $e) {
            Log::error("Failed to process document {$this->document->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Update status to failed
            $this->document->update(['status' => 'failed']);

            // Re-throw the exception to trigger job retry
            throw $e;
        }
    }

    /**
     * Split text into chunks of approximately $targetWordsPerChunk words.
     * 
     * @param string $text
     * @param int $targetWordsPerChunk
     * @return array
     */
    protected function splitTextIntoChunks(string $text, int $targetWordsPerChunk = 500): array
    {
        // Split by sentences first (period, exclamation, question mark followed by space or newline)
        $sentences = preg_split('/(?<=[.!?])\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        if (empty($sentences)) {
            // Fallback: split by words if no sentence boundaries found
            $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
            return array_chunk($words, $targetWordsPerChunk);
        }

        $chunks = [];
        $currentChunk = '';
        $currentWordCount = 0;

        foreach ($sentences as $sentence) {
            $sentenceWordCount = str_word_count($sentence, 0);

            // If adding this sentence exceeds target, save current chunk and start new one
            if ($currentWordCount + $sentenceWordCount > $targetWordsPerChunk && $currentWordCount > 0) {
                $chunks[] = trim($currentChunk);
                $currentChunk = $sentence;
                $currentWordCount = $sentenceWordCount;
            } else {
                $currentChunk .= ' ' . $sentence;
                $currentWordCount += $sentenceWordCount;
            }
        }

        // Add the last chunk
        if (!empty(trim($currentChunk))) {
            $chunks[] = trim($currentChunk);
        }

        // Ensure chunks aren't too large (max 2000 characters for API limits)
        $finalChunks = [];
        foreach ($chunks as $chunk) {
            if (strlen($chunk) > 2000) {
                // Split oversized chunks by paragraphs
                $subChunks = $this->splitOversizedChunk($chunk, 2000);
                $finalChunks = array_merge($finalChunks, $subChunks);
            } else {
                $finalChunks[] = $chunk;
            }
        }

        return $finalChunks;
    }

    /**
     * Split an oversized chunk into smaller pieces.
     * 
     * @param string $chunk
     * @param int $maxLength
     * @return array
     */
    protected function splitOversizedChunk(string $chunk, int $maxLength = 2000): array
    {
        $subChunks = [];
        $currentSubChunk = '';
        
        $paragraphs = explode("\n\n", $chunk);
        
        foreach ($paragraphs as $paragraph) {
            if (strlen($currentSubChunk) + strlen($paragraph) > $maxLength && !empty($currentSubChunk)) {
                $subChunks[] = trim($currentSubChunk);
                $currentSubChunk = $paragraph;
            } else {
                $currentSubChunk .= ($currentSubChunk ? "\n\n" : '') . $paragraph;
            }
        }
        
        if (!empty(trim($currentSubChunk))) {
            $subChunks[] = trim($currentSubChunk);
        }
        
        return $subChunks;
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("ProcessDocumentJob failed for document {$this->document->id}", [
            'error' => $exception->getMessage()
        ]);

        $this->document->update(['status' => 'failed']);
    }
}