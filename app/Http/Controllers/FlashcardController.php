<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Flashcard;
use App\Jobs\GenerateFlashcardsJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FlashcardController extends Controller
{
    /**
     * Display all flashcards for a document.
     */
    public function index(Document $document)
    {
        // Verify document belongs to authenticated user
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $flashcards = Flashcard::where('document_id', $document->id)
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('flashcards.index', compact('document', 'flashcards'));
    }

    /**
     * Generate flashcards for a document.
     */
    public function generate(Document $document)
    {
        // Verify document belongs to authenticated user
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Verify document is processed
        if (!$document->isProcessed()) {
            return redirect()
                ->route('documents.show', $document)
                ->with('error', 'Document must be processed before generating flashcards.');
        }

        // Dispatch background job
        GenerateFlashcardsJob::dispatch($document);

        return redirect()
            ->route('flashcards.index', $document)
            ->with('success', 'Flashcards generation started. This may take a moment. Refresh the page to see them.');
    }

    /**
     * Delete a specific flashcard.
     */
    public function destroy(Flashcard $flashcard)
    {
        // Verify flashcard belongs to authenticated user
        if ($flashcard->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $documentId = $flashcard->document_id;
        $flashcard->delete();

        return redirect()
            ->route('flashcards.index', $documentId)
            ->with('success', 'Flashcard deleted successfully.');
    }

    /**
     * Show the flip-card study interface.
     */
    public function show(Flashcard $flashcard)
    {
        // Verify flashcard belongs to authenticated user
        if ($flashcard->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Get all flashcards for this document for navigation
        $allFlashcards = Flashcard::where('document_id', $flashcard->document_id)
            ->where('user_id', Auth::id())
            ->get();

        $currentIndex = $allFlashcards->search(function ($item) use ($flashcard) {
            return $item->id === $flashcard->id;
        });

        return view('flashcards.show', compact('flashcard', 'allFlashcards', 'currentIndex'));
    }

    /**
     * Check if flashcards exist for a document (AJAX).
     */
    public function check(Document $document)
    {
        if ($document->user_id !== Auth::id()) {
            return response()->json(['exists' => false], 403);
        }

        $exists = Flashcard::where('document_id', $document->id)
            ->where('user_id', Auth::id())
            ->exists();

        return response()->json(['exists' => $exists]);
    }
}