@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('documents.show', $document) }}" class="text-blue-600 hover:text-blue-800">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-gray-900">Flashcards</h1>
            </div>
            <p class="text-gray-600 mt-1">{{ $document->filename }}</p>
        </div>
        
        <form action="{{ route('flashcards.generate', $document) }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg hover:from-purple-600 hover:to-purple-700 transition-all shadow-md">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Generate New Flashcards
            </button>
        </form>
    </div>
    
    <!-- Flashcards Grid -->
    @if($flashcards->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($flashcards as $flashcard)
            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300">
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <span class="text-xs font-semibold text-purple-600 bg-purple-100 px-2 py-1 rounded-full">Flashcard</span>
                        <form action="{{ route('flashcards.destroy', $flashcard) }}" method="POST" onsubmit="return confirm('Delete this flashcard?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </form>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 mb-1">Question:</p>
                        <p class="text-gray-900 font-medium">{{ Str::limit($flashcard->question, 100) }}</p>
                    </div>
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-500 mb-1">Answer:</p>
                        <p class="text-gray-700">{{ Str::limit($flashcard->answer, 100) }}</p>
                    </div>
                    
                    <a href="{{ route('flashcards.show', $flashcard) }}" class="inline-flex items-center text-purple-600 hover:text-purple-800 text-sm font-medium">
                        Study This Card
                        <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                        </svg>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        
        <!-- Start Studying Button -->
        <div class="mt-8 text-center">
            <a href="{{ route('flashcards.show', $flashcards->first()) }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg hover:from-purple-600 hover:to-purple-700 transition-all shadow-md font-semibold">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                </svg>
                Start Studying ({{ $flashcards->count() }} cards)
            </a>
        </div>
    @else
        <div class="bg-white rounded-xl shadow-md p-12 text-center">
            <div class="flex flex-col items-center">
                <svg class="w-24 h-24 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Flashcards Yet</h3>
                <p class="text-gray-500 mb-4">Generate flashcards from your lecture notes to start studying</p>
                <form action="{{ route('flashcards.generate', $document) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Generate Flashcards
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection