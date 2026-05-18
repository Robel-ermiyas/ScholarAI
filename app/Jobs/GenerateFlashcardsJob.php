<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\Flashcard;
use App\Services\GeminiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateFlashcardsJob implements ShouldQueue
{
    use Queueable;

    /**
     * The document instance to generate flashcards for.
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
            // Check if document is processed
            if ($this->document->status !== 'processed') {
                throw new \Exception("Document must be processed before generating flashcards. Current status: {$this->document->status}");
            }

            // Load all chunks for this document
            $chunks = $this->document->chunks()
                ->orderBy('chunk_index')
                ->get();

            if ($chunks->isEmpty()) {
                throw new \Exception("No chunks found for document {$this->document->id}");
            }

            // Combine chunks into context text (limit to reasonable size)
            $context = $this->buildContextFromChunks($chunks, 8000);

            // Build the prompt for flashcard generation
            $prompt = $this->buildFlashcardPrompt($context);

            // Call Gemini to generate flashcards
            $jsonResponse = $gemini->generateJson($prompt);

            // Parse the JSON response
            $flashcardsData = json_decode($jsonResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Failed to parse flashcards JSON", [
                    'error' => json_last_error_msg(),
                    'response' => $jsonResponse
                ]);
                throw new \Exception("Invalid JSON response from Gemini: " . json_last_error_msg());
            }

            // Validate response structure
            if (!is_array($flashcardsData)) {
                throw new \Exception("Expected array of flashcards, got: " . gettype($flashcardsData));
            }

            // Limit to maximum 10 flashcards
            $flashcardsData = array_slice($flashcardsData, 0, 10);

            // Delete existing flashcards for this document
            Flashcard::where('document_id', $this->document->id)
                ->where('user_id', $this->document->user_id)
                ->delete();

            // Save new flashcards
            $savedCount = 0;
            foreach ($flashcardsData as $card) {
                // Validate required fields
                if (!isset($card['question']) || !isset($card['answer'])) {
                    Log::warning("Skipping invalid flashcard", ['card' => $card]);
                    continue;
                }

                Flashcard::create([
                    'document_id' => $this->document->id,
                    'user_id' => $this->document->user_id,
                    'question' => trim($card['question']),
                    'answer' => trim($card['answer']),
                ]);

                $savedCount++;
            }

            if ($savedCount === 0) {
                throw new \Exception("No valid flashcards were generated");
            }

            Log::info("Generated {$savedCount} flashcards for document {$this->document->id}");

        } catch (\Exception $e) {
            Log::error("Failed to generate flashcards for document {$this->document->id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Build context from document chunks with size limit.
     * 
     * @param \Illuminate\Support\Collection $chunks
     * @param int $maxChars
     * @return string
     */
    protected function buildContextFromChunks($chunks, int $maxChars = 8000): string
    {
        $context = '';
        foreach ($chunks as $chunk) {
            $chunkText = $chunk->chunk_text;
            
            if (strlen($context) + strlen($chunkText) > $maxChars) {
                // Take remaining characters from this chunk
                $remaining = $maxChars - strlen($context);
                if ($remaining > 100) {
                    $context .= "\n\n" . substr($chunkText, 0, $remaining);
                }
                break;
            }
            
            $context .= ($context ? "\n\n---\n\n" : '') . $chunkText;
        }
        
        return $context;
    }

    /**
     * Build the prompt for flashcard generation.
     * 
     * @param string $context
     * @return string
     */
    protected function buildFlashcardPrompt(string $context): string
    {
        return "Generate exactly 10 flashcards from the following text. Each flashcard should have a question on one side and the answer on the other. Focus on key concepts, definitions, important facts, and relationships.

Requirements:
- Questions should be clear and specific
- Answers should be accurate but concise
- Cover different topics from across the entire text
- Make questions that test understanding, not just trivial facts

Return ONLY a JSON array with exactly 10 objects. Each object must have:
- 'question': string (the flashcard question)
- 'answer': string (the flashcard answer)

Example format:
[
    {\"question\": \"What is photosynthesis?\", \"answer\": \"The process by which plants convert light energy into chemical energy\"},
    {\"question\": \"List the three main types of rocks.\", \"answer\": \"Igneous, sedimentary, and metamorphic\"}
]

Here is the text to generate flashcards from:

{$context}";
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateFlashcardsJob failed for document {$this->document->id}", [
            'error' => $exception->getMessage()
        ]);
    }
}