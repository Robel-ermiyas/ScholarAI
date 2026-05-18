<?php

namespace App\Jobs;

use App\Models\Document;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Services\GeminiService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class GenerateQuizJob implements ShouldQueue
{
    use Queueable;

    /**
     * The document instance to generate quiz for.
     */
    protected Document $document;

    /**
     * Optional custom title for the quiz.
     */
    protected ?string $customTitle;

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
    public function __construct(Document $document, ?string $customTitle = null)
    {
        $this->document = $document;
        $this->customTitle = $customTitle;
    }

    /**
     * Execute the job.
     */
    public function handle(GeminiService $gemini): void
    {
        try {
            // Check if document is processed
            if ($this->document->status !== 'processed') {
                throw new \Exception("Document must be processed before generating quiz. Current status: {$this->document->status}");
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

            // Build the prompt for quiz generation
            $prompt = $this->buildQuizPrompt($context);

            // Call Gemini to generate quiz questions
            $jsonResponse = $gemini->generateJson($prompt);

            // Parse the JSON response
            $questionsData = json_decode($jsonResponse, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Failed to parse quiz JSON", [
                    'error' => json_last_error_msg(),
                    'response' => $jsonResponse
                ]);
                throw new \Exception("Invalid JSON response from Gemini: " . json_last_error_msg());
            }

            // Validate response structure
            if (!is_array($questionsData)) {
                throw new \Exception("Expected array of questions, got: " . gettype($questionsData));
            }

            // Limit to maximum 10 questions
            $questionsData = array_slice($questionsData, 0, 10);

            if (empty($questionsData)) {
                throw new \Exception("No questions were generated");
            }

            // Generate quiz title
            $quizTitle = $this->customTitle ?: $this->generateQuizTitle();

            // Create the quiz record
            $quiz = Quiz::create([
                'document_id' => $this->document->id,
                'user_id' => $this->document->user_id,
                'title' => $quizTitle,
            ]);

            // Save questions
            $savedCount = 0;
            foreach ($questionsData as $index => $qData) {
                // Validate required fields
                if (!isset($qData['question']) || !isset($qData['options']) || !isset($qData['correct_answer'])) {
                    Log::warning("Skipping invalid quiz question", ['question_data' => $qData]);
                    continue;
                }

                // Validate options is an array with 4 items
                $options = $qData['options'];
                if (!is_array($options) || count($options) !== 4) {
                    Log::warning("Skipping question - invalid options count", ['options' => $options]);
                    continue;
                }

                // Validate correct_answer is A, B, C, or D
                $correctAnswer = strtoupper(trim($qData['correct_answer']));
                if (!in_array($correctAnswer, ['A', 'B', 'C', 'D'])) {
                    Log::warning("Skipping question - invalid correct_answer", ['correct_answer' => $correctAnswer]);
                    continue;
                }

                // Clean up options (remove the letter prefix if present)
                $cleanOptions = array_map(function($option, $index) {
                    $letters = ['A', 'B', 'C', 'D'];
                    $prefix = $letters[$index] . ')';
                    // Remove "A) ", "A. ", "A: " etc.
                    $cleaned = preg_replace('/^[' . implode('', $letters) . '][\.\)\:]\s*/', '', $option);
                    return trim($cleaned);
                }, $options, array_keys($options));

                QuizQuestion::create([
                    'quiz_id' => $quiz->id,
                    'question' => trim($qData['question']),
                    'options' => $cleanOptions,
                    'correct_answer' => $correctAnswer,
                ]);

                $savedCount++;
            }

            if ($savedCount === 0) {
                // Delete the quiz if no questions were saved
                $quiz->delete();
                throw new \Exception("No valid quiz questions were generated");
            }

            Log::info("Generated quiz '{$quizTitle}' with {$savedCount} questions for document {$this->document->id}");

        } catch (\Exception $e) {
            Log::error("Failed to generate quiz for document {$this->document->id}", [
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
                $remaining = $maxChars - strlen($context);
                if ($remaining > 100) {
                    $context .= "\n\n---\n\n" . substr($chunkText, 0, $remaining);
                }
                break;
            }
            
            $context .= ($context ? "\n\n---\n\n" : '') . $chunkText;
        }
        
        return $context;
    }

    /**
     * Build the prompt for quiz generation.
     * 
     * @param string $context
     * @return string
     */
    protected function buildQuizPrompt(string $context): string
    {
        return "Generate exactly 10 multiple-choice questions from the following text. Each question should test understanding of key concepts, definitions, important facts, and relationships.

Requirements:
- Questions should be clear and unambiguous
- Each question must have exactly 4 options (A, B, C, D)
- Only ONE correct answer per question
- Distractors (wrong answers) should be plausible but clearly incorrect
- Cover different topics from across the entire text
- Vary difficulty (some easy, some medium, some challenging)

Return ONLY a JSON array with exactly 10 objects. Each object must have:
- 'question': string (the question text)
- 'options': array of exactly 4 strings (the answer choices)
- 'correct_answer': string (must be 'A', 'B', 'C', or 'D')

Example format:
[
    {
        \"question\": \"What is the capital of France?\",
        \"options\": [\"A) London\", \"B) Berlin\", \"C) Paris\", \"D) Madrid\"],
        \"correct_answer\": \"C\"
    },
    {
        \"question\": \"Which planet is known as the Red Planet?\",
        \"options\": [\"A) Jupiter\", \"B) Mars\", \"C) Venus\", \"D) Saturn\"],
        \"correct_answer\": \"B\"
    }
]

Here is the text to generate questions from:

{$context}";
    }

    /**
     * Generate a title for the quiz.
     * 
     * @return string
     */
    protected function generateQuizTitle(): string
    {
        // Get the original filename without extension
        $filename = pathinfo($this->document->filename, PATHINFO_FILENAME);
        
        // Truncate if too long
        if (strlen($filename) > 50) {
            $filename = substr($filename, 0, 47) . '...';
        }
        
        return $filename . ' - Quiz ' . now()->format('Y-m-d H:i');
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("GenerateQuizJob failed for document {$this->document->id}", [
            'error' => $exception->getMessage()
        ]);
    }
}