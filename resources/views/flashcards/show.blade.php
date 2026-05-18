@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('documents.show', $flashcard->document) }}" class="text-blue-600 hover:text-blue-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Flashcards</h1>
            </div>
            <p class="text-gray-600 mt-1">{{ $flashcard->document->filename }}</p>
        </div>
        <div class="flex space-x-3">
            <form action="{{ route('flashcards.generate', $flashcard->document) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg hover:from-purple-600 hover:to-purple-700 transition-all shadow-md">
                    <svg class="w-5 h-5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    Regenerate
                </button>
            </form>
            <form action="{{ route('flashcards.destroy', $flashcard) }}" method="POST" onsubmit="return confirm('Delete this flashcard?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 border border-red-300 text-red-600 rounded-lg hover:bg-red-50 transition-colors">
                    Delete Card
                </button>
            </form>
        </div>
    </div>
    
    <!-- Flashcard Counter -->
    <div class="text-center mb-4">
        <p class="text-gray-600">
            Card <span id="currentCardIndex">{{ $currentIndex + 1 }}</span> of <span id="totalCards">{{ $allFlashcards->count() }}</span>
        </p>
    </div>
    
    <!-- Flip Card -->
    <div class="flip-card" id="flipCard">
        <div class="flip-card-inner">
            <!-- Front (Question) -->
            <div class="flip-card-front flex items-center justify-center p-8">
                <div class="text-center">
                    <div class="text-white text-opacity-75 text-sm uppercase tracking-wide mb-4">Question</div>
                    <p class="text-xl font-medium leading-relaxed" id="questionText">{{ $flashcard->question }}</p>
                    <div class="absolute bottom-8 left-0 right-0 text-center text-white text-opacity-50 text-sm">
                        Click to flip →
                    </div>
                </div>
            </div>
            <!-- Back (Answer) -->
            <div class="flip-card-back flex items-center justify-center p-8">
                <div class="text-center">
                    <div class="text-white text-opacity-75 text-sm uppercase tracking-wide mb-4">Answer</div>
                    <p class="text-xl font-medium leading-relaxed" id="answerText">{{ $flashcard->answer }}</p>
                    <div class="absolute bottom-8 left-0 right-0 text-center text-white text-opacity-50 text-sm">
                        Click to flip ←
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Navigation Buttons -->
    <div class="flex justify-between items-center mt-8 space-x-4">
        <button id="prevBtn" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors disabled:opacity-50 disabled:cursor-not-allowed font-medium">
            ← Previous
        </button>
        <button id="nextBtn" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all shadow-md disabled:opacity-50 disabled:cursor-not-allowed font-medium">
            Next →
        </button>
    </div>
    
    <!-- Progress Bar -->
    <div class="mt-6 bg-gray-200 rounded-full h-2 overflow-hidden">
        <div id="progressBar" class="bg-gradient-to-r from-blue-500 to-purple-500 h-2 transition-all duration-300" style="width: {{ (($currentIndex + 1) / $allFlashcards->count()) * 100 }}%"></div>
    </div>
</div>

@push('scripts')
<script>
    const flashcards = @json($allFlashcards->toArray());
    let currentCardId = {{ $flashcard->id }};
    let currentIndex = {{ $currentIndex }};
    const totalCards = flashcards.length;
    
    const flipCard = document.getElementById('flipCard');
    const questionText = document.getElementById('questionText');
    const answerText = document.getElementById('answerText');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const currentCardIndexSpan = document.getElementById('currentCardIndex');
    const progressBar = document.getElementById('progressBar');
    
    // Flip card on click
    flipCard.addEventListener('click', function() {
        this.classList.toggle('flipped');
    });
    
    // Update card content
    function updateCard(index) {
        const card = flashcards[index];
        if (!card) return;
        
        currentCardId = card.id;
        currentIndex = index;
        
        // Update text
        questionText.textContent = card.question;
        answerText.textContent = card.answer;
        
        // Reset flip state
        flipCard.classList.remove('flipped');
        
        // Update counter
        currentCardIndexSpan.textContent = index + 1;
        
        // Update progress bar
        const progress = ((index + 1) / totalCards) * 100;
        progressBar.style.width = progress + '%';
        
        // Update URL without reload
        const url = new URL(window.location.href);
        url.searchParams.set('card', card.id);
        window.history.pushState({}, '', url);
        
        // Update button states
        prevBtn.disabled = index === 0;
        nextBtn.disabled = index === totalCards - 1;
    }
    
    // Previous card
    prevBtn.addEventListener('click', function() {
        if (currentIndex > 0) {
            updateCard(currentIndex - 1);
        }
    });
    
    // Next card
    nextBtn.addEventListener('click', function() {
        if (currentIndex < totalCards - 1) {
            updateCard(currentIndex + 1);
        }
    });
    
    // Keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'ArrowLeft' && currentIndex > 0) {
            updateCard(currentIndex - 1);
        } else if (e.key === 'ArrowRight' && currentIndex < totalCards - 1) {
            updateCard(currentIndex + 1);
        } else if (e.key === ' ' || e.key === 'Spacebar' || e.key === 'Space') {
            e.preventDefault();
            flipCard.classList.toggle('flipped');
        }
    });
</script>
@endpush
@endsection