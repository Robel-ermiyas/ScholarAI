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
                <h1 class="text-3xl font-bold text-gray-900">Quizzes</h1>
            </div>
            <p class="text-gray-600 mt-1">{{ $document->filename }}</p>
        </div>
        
        <form action="{{ route('quiz.generate', $document) }}" method="POST">
            @csrf
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition-all shadow-md">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Generate New Quiz
            </button>
        </form>
    </div>
    
    <!-- Quizzes Grid -->
    @if($quizzes->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @foreach($quizzes as $quiz)
            <div class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-all duration-300">
                <div class="bg-gradient-to-r from-green-50 to-teal-50 px-6 py-4 border-b border-gray-200">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">{{ $quiz->title }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $quiz->questions_count }} questions</p>
                        </div>
                        <div class="flex space-x-2">
                            @if($quiz->latest_attempt)
                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold
                                    @if($quiz->latest_attempt->percentage >= 70) bg-green-100 text-green-800
                                    @elseif($quiz->latest_attempt->percentage >= 50) bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                    Last: {{ $quiz->latest_attempt->score }}/{{ $quiz->latest_attempt->total }}
                                </span>
                            @endif
                            <form action="{{ route('quiz.destroy', $quiz) }}" method="POST" onsubmit="return confirm('Delete this quiz?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-500 hover:text-red-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="p-6">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            Created {{ $quiz->created_at->diffForHumans() }}
                        </div>
                        <div class="space-x-3">
                            <a href="{{ route('quiz.show', $quiz) }}" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm font-medium">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                </svg>
                                Take Quiz
                            </a>
                            <a href="{{ route('quiz.history', $quiz) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                                History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="bg-white rounded-xl shadow-md p-12 text-center">
            <div class="flex flex-col items-center">
                <svg class="w-24 h-24 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">No Quizzes Yet</h3>
                <p class="text-gray-500 mb-4">Generate a quiz from your lecture notes to test your knowledge</p>
                <form action="{{ route('quiz.generate', $document) }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Generate Quiz
                    </button>
                </form>
            </div>
        </div>
    @endif
</div>
@endsection