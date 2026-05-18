<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Jobs\ProcessDocumentJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class DocumentController extends Controller
{
    /**
     * Display a listing of the user's documents.
     */
    public function index()
    {
        $documents = Document::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('documents.index', compact('documents'));
    }

    /**
     * Show the form for creating a new document.
     */
    public function create()
    {
        return view('documents.create');
    }

    /**
     * Store a newly created document.
     */
    public function store(Request $request)
    {
        $request->validate([
            'pdf_file' => [
                'required',
                'file',
                'mimes:pdf',
                'max:10240', // 10MB in kilobytes
            ],
        ]);

        $file = $request->file('pdf_file');
        $userId = Auth::id();
        
        // Generate a unique filename to avoid collisions
        $originalName = $file->getClientOriginalName();
        $safeName = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $originalName);
        
        // Store file in private disk under user_id folder
        $path = $file->storeAs("{$userId}", $safeName, 'private');
        
        if (!$path) {
            return back()->withErrors(['pdf_file' => 'Failed to upload file.']);
        }

        // Create document record
        $document = Document::create([
            'user_id' => $userId,
            'filename' => $originalName,
            'path' => $path,
            'status' => 'pending',
        ]);

        // Dispatch background job for processing
        ProcessDocumentJob::dispatch($document);

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document uploaded and is being processed. You will be notified when it\'s ready.');
    }

    /**
     * Remove the specified document.
     */
    public function destroy(Document $document)
    {
        // Verify document belongs to authenticated user
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Delete the physical file from storage
        if (Storage::disk('private')->exists($document->path)) {
            Storage::disk('private')->delete($document->path);
        }

        // Delete the document record (cascade will handle chunks, chats, etc.)
        $document->delete();

        return redirect()
            ->route('documents.index')
            ->with('success', 'Document deleted successfully.');
    }

    /**
     * Display document details (chat, flashcards, quiz options).
     */
    public function show(Document $document)
    {
        // Verify document belongs to authenticated user
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Load counts for UI
        $flashcardCount = $document->flashcards()->count();
        $quizCount = $document->quizzes()->count();
        $chatSessionCount = $document->chatSessions()->count();

        return view('documents.show', compact(
            'document',
            'flashcardCount',
            'quizCount',
            'chatSessionCount'
        ));
    }
}