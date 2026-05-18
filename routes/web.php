<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\QuizController;
use Illuminate\Support\Facades\Route;

// Home route - Show welcome page for guests, dashboard for logged in users
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Document Management
    Route::resource('documents', DocumentController::class)->except(['edit', 'update']);
    Route::get('/documents/{document}/show', [DocumentController::class, 'show'])->name('documents.show');
    
    // Chat Routes
    Route::prefix('documents/{document}/chat')->group(function () {
        Route::get('/', [ChatController::class, 'index'])->name('chat.index');
        Route::post('/', [ChatController::class, 'store'])->name('chat.store');
    });
    
    Route::prefix('chat')->group(function () {
        Route::get('/{session}', [ChatController::class, 'show'])->name('chat.show');
        Route::post('/{session}/ask', [ChatController::class, 'ask'])->name('chat.ask');
        Route::put('/{session}/title', [ChatController::class, 'updateTitle'])->name('chat.updateTitle');
        Route::delete('/{session}', [ChatController::class, 'destroy'])->name('chat.destroy');
    });
    
    // Flashcard Routes
    Route::prefix('documents/{document}/flashcards')->group(function () {
        Route::get('/', [FlashcardController::class, 'index'])->name('flashcards.index');
        Route::post('/generate', [FlashcardController::class, 'generate'])->name('flashcards.generate');
        Route::get('/check', [FlashcardController::class, 'check'])->name('flashcards.check');
    });
    
    Route::prefix('flashcards')->group(function () {
        Route::get('/{flashcard}', [FlashcardController::class, 'show'])->name('flashcards.show');
        Route::delete('/{flashcard}', [FlashcardController::class, 'destroy'])->name('flashcards.destroy');
    });
    
    // Quiz Routes
    Route::prefix('documents/{document}/quizzes')->group(function () {
        Route::get('/', [QuizController::class, 'index'])->name('quiz.index');
        Route::post('/generate', [QuizController::class, 'generate'])->name('quiz.generate');
    });
    
    Route::prefix('quizzes')->group(function () {
        Route::get('/{quiz}', [QuizController::class, 'show'])->name('quiz.show');
        Route::post('/{quiz}/submit', [QuizController::class, 'submit'])->name('quiz.submit');
        Route::delete('/{quiz}', [QuizController::class, 'destroy'])->name('quiz.destroy');
        Route::get('/{quiz}/history', [QuizController::class, 'history'])->name('quiz.history');
    });
    
    Route::get('/quiz-attempts/{attempt}', [QuizController::class, 'result'])->name('quiz.result');
});

// Include authentication routes (provided by Laravel Breeze)
require __DIR__.'/auth.php';