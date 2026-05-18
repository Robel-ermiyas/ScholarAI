<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    /**
     * The base URL for Gemini API.
     */
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1/models/';

    /**
     * Gemini API key.
     */
    protected string $apiKey;

    /**
     * Chat model name.
     */
    protected string $chatModel;

    /**
     * Embedding model name.
     */
    protected string $embedModel;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->chatModel = config('services.gemini.chat_model', 'gemini-1.5-flash');
        $this->embedModel = config('services.gemini.embed_model', 'text-embedding-004');
    }

    /**
     * Generate embeddings for a given text.
     */
    public function embed(string $text): array
    {
        // v1beta is correct specifically for embedContent endpoint
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $this->embedModel . ':embedContent?key=' . $this->apiKey;

        $payload = [
            'content' => [
                'parts' => [
                    ['text' => $text]
                ]
            ]
        ];

        try {
            $response = Http::timeout(30)->post($url, $payload);

            if ($response->failed()) {
                Log::error('Gemini Embedding API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('Failed to generate embeddings: ' . $response->body());
            }

            $data = $response->json();

            if (!isset($data['embedding']['values'])) {
                throw new \Exception('Invalid embedding response format');
            }

            return $data['embedding']['values'];

        } catch (\Exception $e) {
            Log::error('Gemini Embedding Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Generate chat response with conversation history.
     */
    public function chatWithHistory(string $systemPrompt, array $history): string
    {
        $url = $this->baseUrl . $this->chatModel . ':generateContent?key=' . $this->apiKey;

        $contents = [];

        // Add system prompt as first user message followed by model acceptance
        $contents[] = [
            'role' => 'user',
            'parts' => [['text' => $systemPrompt]]
        ];
        $contents[] = [
            'role' => 'model',
            'parts' => [['text' => "I understand. I will only answer based on the provided context and notes."]]
        ];

        // Convert history to Gemini format
        foreach ($history as $message) {
            $role = $message['role'];
            $content = $message['content'];

            if (empty($content)) {
                continue;
            }

            // Map 'assistant' to 'model' for Gemini
            $geminiRole = ($role === 'assistant') ? 'model' : 'user';

            $contents[] = [
                'role' => $geminiRole,
                'parts' => [['text' => $content]]
            ];
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.3,
                'maxOutputTokens' => 2048,
                'topP' => 0.95,
                'topK' => 40,
            ]
        ];

        try {
            $response = Http::timeout(60)->post($url, $payload);

            if ($response->failed()) {
                Log::error('Gemini Chat API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                throw new \Exception('Failed to generate chat response: ' . $response->body());
            }

            $data = $response->json();

            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception('Invalid chat response format');
            }

            return trim($data['candidates'][0]['content']['parts'][0]['text']);

        } catch (\Exception $e) {
            Log::error('Gemini Chat Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Generate chat response with context and history.
     */
    public function chatWithContext(string $systemPrompt, array $history, array $contextChunks): string
    {
        $context = implode("\n\n---\n\n", $contextChunks);

        $fullPrompt = $systemPrompt . "\n\n**CONTEXT FROM NOTES:**\n" . $context . "\n\n**CONVERSATION HISTORY:**\n";

        if (empty($history)) {
            return $this->chatWithHistory($fullPrompt, []);
        }

        $lastUserIndex = null;
        foreach (array_reverse($history, true) as $index => $message) {
            if ($message['role'] === 'user') {
                $lastUserIndex = $index;
                break;
            }
        }

        if ($lastUserIndex !== null) {
            $modifiedHistory = $history;
            $modifiedHistory[$lastUserIndex]['content'] =
                "Context from notes:\n" . $context . "\n\n" .
                "Question: " . $modifiedHistory[$lastUserIndex]['content'];
            return $this->chatWithHistory($fullPrompt, $modifiedHistory);
        }

        return $this->chatWithHistory($fullPrompt, $history);
    }

    /**
     * Calculate cosine similarity between two vectors.
     */
    public function cosineSimilarity(array $a, array $b): float
    {
        if (empty($a) || empty($b) || count($a) !== count($b)) {
            return 0.0;
        }

        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        for ($i = 0; $i < count($a); $i++) {
            $valA = (float)$a[$i];
            $valB = (float)$b[$i];

            $dotProduct += $valA * $valB;
            $normA += $valA * $valA;
            $normB += $valB * $valB;
        }

        if ($normA == 0.0 || $normB == 0.0) {
            return 0.0;
        }

        $similarity = $dotProduct / (sqrt($normA) * sqrt($normB));

        return max(0.0, min(1.0, $similarity));
    }

    /**
     * Generate JSON response from Gemini.
     */
    public function generateJson(string $prompt): string
    {
        $url = $this->baseUrl . $this->chatModel . ':generateContent?key=' . $this->apiKey;

        $payload = [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $prompt . "\n\nRespond with ONLY valid JSON. No other text, explanations, or markdown formatting."]
                    ]
                ]
            ],
            'generationConfig' => [
                'temperature' => 0.2,
                'maxOutputTokens' => 4096,
            ]
        ];

        try {
            $response = Http::timeout(60)->post($url, $payload);

            if ($response->failed()) {
                throw new \Exception('Failed to generate JSON: ' . $response->body());
            }

            $data = $response->json();

            if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                throw new \Exception('Invalid response format');
            }

            $text = trim($data['candidates'][0]['content']['parts'][0]['text']);

            // Remove markdown code blocks if present
            $text = preg_replace('/^```json\s*|\s*```$/i', '', $text);
            $text = preg_replace('/^```\s*|\s*```$/i', '', $text);

            return $text;

        } catch (\Exception $e) {
            Log::error('Gemini JSON Generation Exception', ['message' => $e->getMessage()]);
            throw $e;
        }
    }
}