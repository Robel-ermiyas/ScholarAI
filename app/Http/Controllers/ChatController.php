<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\ChatMessage;
use App\Models\Document;
use App\Services\GeminiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    protected GeminiService $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    /**
     * Display all chat sessions for a document.
     */
    public function index(Document $document)
    {
        // Verify document belongs to authenticated user
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Verify document is processed
        if (!$document->isProcessed()) {
            return redirect()
                ->route('documents.show', $document)
                ->with('error', 'Document is still processing. Please wait until processing is complete.');
        }

        $sessions = ChatSession::where('document_id', $document->id)
            ->where('user_id', Auth::id())
            ->with('latestMessage')
            ->latest()
            ->get();

        return view('chat.index', compact('document', 'sessions'));
    }

    /**
     * Create a new chat session for a document.
     */
    public function store(Document $document)
    {
        // Verify document belongs to authenticated user
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Verify document is processed
        if (!$document->isProcessed()) {
            return redirect()
                ->route('documents.show', $document)
                ->with('error', 'Document must be processed before chatting.');
        }

        $session = ChatSession::create([
            'user_id' => Auth::id(),
            'document_id' => $document->id,
            'title' => 'New conversation',
        ]);

        return redirect()->route('chat.show', $session);
    }

    /**
     * Display a specific chat session with all messages.
     */
    public function show(ChatSession $session)
    {
        // Verify session belongs to authenticated user
        if ($session->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $messages = $session->messages()
            ->orderBy('created_at', 'asc')
            ->get();

        return view('chat.show', compact('session', 'messages'));
    }

    /**
     * Process a user's question and return AI response.
     */
    public function ask(Request $request, ChatSession $session)
    {
        // Verify session belongs to authenticated user
        if ($session->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Validate request
        $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $userMessage = trim($request->message);

        // Quick handling for common conversational queries (allow general replies)
        $generalPatterns = [
            '/^hi$|^hello$|^hey$/i',
            '/who are you\??/i',
            '/what can you do\??/i',
            '/what is your name\??/i',
            '/help\b/i',
            '/how are you\??/i',
            '/thank(s| you)\b/i',
        ];

        foreach ($generalPatterns as $pattern) {
            if (preg_match($pattern, $userMessage)) {
                $friendly = "Hello! I'm ScholarAI — your study assistant. I can answer questions based on your uploaded notes, generate flashcards, and create quizzes. Ask me anything about your notes or say 'help' to get started.";

                // Save user message already saved above, now save assistant reply
                $assistantMessageRecord = ChatMessage::create([
                    'session_id' => $session->id,
                    'role' => 'assistant',
                    'content' => $friendly,
                ]);

                if ($session->title === 'New conversation') {
                    $newTitle = strlen($userMessage) > 50
                        ? substr($userMessage, 0, 47) . '...'
                        : $userMessage;
                    $session->update(['title' => $newTitle]);
                }

                return response()->json([
                    'success' => true,
                    'message' => $friendly,
                    'message_id' => $assistantMessageRecord->id,
                ]);
            }
        }

        // Save user message
        $userMessageRecord = ChatMessage::create([
            'session_id' => $session->id,
            'role' => 'user',
            'content' => $userMessage,
        ]);

        try {
            // Step 1: Embed the user's question
            $questionEmbedding = $this->gemini->embed($userMessage);

            // Step 2: Load all chunks for the document
            $chunks = $session->document->chunks()
                ->whereNotNull('embedding')
                ->get();

            if ($chunks->isEmpty()) {
                throw new \Exception('No processed chunks found for this document.');
            }

            // Step 3: Score each chunk using cosine similarity
            $scoredChunks = [];
            foreach ($chunks as $chunk) {
                // $chunkEmbedding = json_decode($chunk->embedding, true);
                $chunkEmbedding = is_array($chunk->embedding) ? $chunk->embedding : json_decode($chunk->embedding, true);
                if (is_array($chunkEmbedding) && count($chunkEmbedding) > 0) {
                    $similarity = $this->gemini->cosineSimilarity($questionEmbedding, $chunkEmbedding);
                    $scoredChunks[] = [
                        'text' => $chunk->chunk_text,
                        'similarity' => $similarity,
                    ];
                }
            }

            // Step 4: Sort by similarity (highest first) and take top 4
            usort($scoredChunks, function ($a, $b) {
                return $b['similarity'] <=> $a['similarity'];
            });
            
            $topChunks = array_slice($scoredChunks, 0, 4);
            $contextChunks = array_column($topChunks, 'text');

            // Step 5: Load chat history
            $chatHistory = ChatMessage::where('session_id', $session->id)
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(function ($message) {
                    return [
                        'role' => $message->role,
                        'content' => $message->content,
                    ];
                })
                ->toArray();

            // Step 6: Build system prompt
            $systemPrompt = "You are ScholarAI, a helpful study assistant. You answer questions based ONLY on the provided lecture notes context. Follow these rules strictly:

1. ONLY use information from the context provided below to answer questions.
2. If the answer cannot be found in the context, say: \"I couldn't find that in your lecture notes. Please check if the information is covered in another document or rephrase your question.\"
3. Do NOT use any external knowledge or prior training data.
4. Be concise, clear, and educational in your responses.
5. If the context is relevant, cite specific parts when helpful.
6. Never invent information or make assumptions beyond the context.

Context from the user's lecture notes will be provided before each question.";

            // Step 7: Get AI response
            $aiResponse = $this->gemini->chatWithContext(
                $systemPrompt,
                $chatHistory,
                $contextChunks
            );

            // Step 8: Save assistant response
            $assistantMessageRecord = ChatMessage::create([
                'session_id' => $session->id,
                'role' => 'assistant',
                'content' => $aiResponse,
            ]);

            // Update session title if it's still the default and this is the first message
            if ($session->title === 'New conversation') {
                // Generate title from first user message (truncate)
                $newTitle = strlen($userMessage) > 50 
                    ? substr($userMessage, 0, 47) . '...' 
                    : $userMessage;
                $session->update(['title' => $newTitle]);
            }

            return response()->json([
                'success' => true,
                'message' => $aiResponse,
                'message_id' => $assistantMessageRecord->id,
            ]);

        } catch (\Exception $e) {
            // Delete the user message if AI failed
            $userMessageRecord->delete();

            return response()->json([
                'success' => false,
                'error' => 'Failed to generate response: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update chat session title.
     */
    public function updateTitle(Request $request, ChatSession $session)
    {
        // Verify session belongs to authenticated user
        if ($session->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $session->update(['title' => $request->title]);

        return response()->json(['success' => true]);
    }

    /**
     * Delete a chat session.
     */
    public function destroy(ChatSession $session)
    {
        // Verify session belongs to authenticated user
        if ($session->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $documentId = $session->document_id;
        $session->delete();

        return redirect()
            ->route('chat.index', $documentId)
            ->with('success', 'Chat session deleted.');
    }
}