<?php

namespace App\Http\Controllers;

use App\Models\ChatSession;
use App\Models\Document;
use App\Models\Flashcard;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Show the user's study dashboard.
     */
    public function index()
    {
        $user = Auth::user();

        // Total counts
        $totalDocuments = Document::where('user_id', $user->id)->count();
        $totalFlashcards = Flashcard::where('user_id', $user->id)->count();
        $totalQuizzes = Quiz::where('user_id', $user->id)->count();

        // Last 5 chat sessions with document name
        $recentChats = ChatSession::where('user_id', $user->id)
            ->with('document')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($session) {
                return [
                    'id' => $session->id,
                    'title' => $session->title,
                    'document_name' => $session->document->filename,
                    'created_at' => $session->created_at,
                ];
            });

        // Last 5 quiz attempts with score, total, and document name
        $recentQuizAttempts = QuizAttempt::where('user_id', $user->id)
            ->with('quiz.document')
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($attempt) {
                return [
                    'id' => $attempt->id,
                    'quiz_title' => $attempt->quiz->title,
                    'document_name' => $attempt->quiz->document->filename,
                    'score' => $attempt->score,
                    'total' => $attempt->total,
                    'percentage' => $attempt->percentage,
                    'passed' => $attempt->passed,
                    'created_at' => $attempt->created_at,
                ];
            });

        // All documents with their status
        $documents = Document::where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'filename' => $doc->filename,
                    'status' => $doc->status,
                    'created_at' => $doc->created_at,
                    'is_processed' => $doc->isProcessed(),
                ];
            });

        return view('dashboard', compact(
            'totalDocuments',
            'totalFlashcards',
            'totalQuizzes',
            'recentChats',
            'recentQuizAttempts',
            'documents'
        ));
    }
}