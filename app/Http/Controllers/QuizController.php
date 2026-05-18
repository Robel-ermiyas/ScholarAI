<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizQuestion;
use App\Jobs\GenerateQuizJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    /**
     * Display all quizzes for a document.
     */
    public function index(Document $document)
    {
        // Verify document belongs to authenticated user
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $quizzes = Quiz::where('document_id', $document->id)
            ->where('user_id', Auth::id())
            ->withCount('questions')
            ->latest()
            ->get();

        // Get latest attempt for each quiz
        foreach ($quizzes as $quiz) {
            $quiz->latest_attempt = QuizAttempt::where('quiz_id', $quiz->id)
                ->where('user_id', Auth::id())
                ->latest()
                ->first();
        }

        return view('quiz.index', compact('document', 'quizzes'));
    }

    /**
     * Generate a new quiz for a document.
     */
    public function generate(Request $request, Document $document)
    {
        // Verify document belongs to authenticated user
        if ($document->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        // Verify document is processed
        if (!$document->isProcessed()) {
            return redirect()
                ->route('documents.show', $document)
                ->with('error', 'Document must be processed before generating a quiz.');
        }

        $customTitle = $request->input('title');

        // Dispatch background job
        GenerateQuizJob::dispatch($document, $customTitle);

        return redirect()
            ->route('quiz.index', $document)
            ->with('success', 'Quiz generation started. This may take a moment. Refresh the page to see it.');
    }

    /**
     * Display a quiz for taking.
     */
    public function show(Quiz $quiz)
    {
        // Verify quiz belongs to authenticated user
        if ($quiz->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $questions = $quiz->questions()
            ->orderBy('id')
            ->get();

        // Check if user has attempted this quiz before
        $previousAttempt = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', Auth::id())
            ->latest()
            ->first();

        return view('quiz.show', compact('quiz', 'questions', 'previousAttempt'));
    }

    /**
     * Submit quiz answers and calculate score.
     */
    public function submit(Request $request, Quiz $quiz)
    {
        // Verify quiz belongs to authenticated user
        if ($quiz->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'answers' => 'required|array',
            'answers.*' => 'nullable|string|in:A,B,C,D',
        ]);

        $submittedAnswers = $request->input('answers', []);
        $questions = $quiz->questions()->get();
        
        $score = 0;
        $total = $questions->count();

        // Calculate score
        foreach ($questions as $index => $question) {
            $submittedAnswer = $submittedAnswers[$index] ?? null;
            if ($submittedAnswer && $question->isCorrect($submittedAnswer)) {
                $score++;
            }
        }

        // Save the attempt
        $attempt = QuizAttempt::create([
            'quiz_id' => $quiz->id,
            'user_id' => Auth::id(),
            'score' => $score,
            'total' => $total,
        ]);

        // Load questions with user answers for result page
        $results = [];
        foreach ($questions as $index => $question) {
            $submittedAnswer = $submittedAnswers[$index] ?? null;
            $results[] = [
                'question' => $question->question,
                'options' => $question->options,
                'correct_answer' => $question->correct_answer,
                'user_answer' => $submittedAnswer,
                'is_correct' => $submittedAnswer && $question->isCorrect($submittedAnswer),
            ];
        }

        // Store results in session for the result page
        session()->flash('quiz_results', $results);

        return redirect()->route('quiz.result', $attempt);
    }

    /**
     * Display quiz results.
     */
    public function result(QuizAttempt $attempt)
    {
        // Verify attempt belongs to authenticated user
        if ($attempt->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $quiz = $attempt->quiz;
        $quiz->load('document');
        
        // Get results from session or build them
        $results = session()->get('quiz_results', []);
        
        if (empty($results)) {
            // Rebuild results from attempt if not in session
            $questions = $quiz->questions()->get();
            $results = [];
            foreach ($questions as $question) {
                $results[] = [
                    'question' => $question->question,
                    'options' => $question->options,
                    'correct_answer' => $question->correct_answer,
                    'user_answer' => null,
                    'is_correct' => false,
                ];
            }
        }

        return view('quiz.result', compact('attempt', 'quiz', 'results'));
    }

    /**
     * Delete a quiz.
     */
    public function destroy(Quiz $quiz)
    {
        // Verify quiz belongs to authenticated user
        if ($quiz->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $documentId = $quiz->document_id;
        $quiz->delete();

        return redirect()
            ->route('quiz.index', $documentId)
            ->with('success', 'Quiz deleted successfully.');
    }

    /**
     * Show attempt history for a quiz.
     */
    public function history(Quiz $quiz)
    {
        // Verify quiz belongs to authenticated user
        if ($quiz->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $attempts = QuizAttempt::where('quiz_id', $quiz->id)
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('quiz.history', compact('quiz', 'attempts'));
    }
}