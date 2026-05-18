@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Score Card -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-6 py-8 text-center">
            <h2 class="text-2xl font-bold text-white mb-4">Quiz Complete!</h2>
            <div class="inline-flex flex-col items-center">
                <div class="text-6xl font-bold text-white mb-2">
                    {{ $attempt->score }}/{{ $attempt->total }}
                </div>
                <div class="text-xl text-gray-300 mb-4">{{ $attempt->percentage }}%</div>
                <div class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold
                    @if($attempt->percentage >= 70) bg-green-500 text-white
                    @elseif($attempt->percentage >= 50) bg-yellow-500 text-white
                    @else bg-red-500 text-white @endif">
                    @if($attempt->percentage >= 70)
                        Excellent! 🎉
                    @elseif($attempt->percentage >= 50)
                        Good Effort! 📚
                    @else
                        Keep Studying! 💪
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="px-6 py-4 bg-gray-50 flex justify-center space-x-4">
            <a href="{{ route('quiz.show', $attempt->quiz) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                Retake Quiz
            </a>
            <a href="{{ route('quiz.index', $attempt->quiz->document) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                Back to Quizzes
            </a>
            <a href="{{ route('documents.show', $attempt->quiz->document) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors">
                Document Overview
            </a>
        </div>
    </div>
    
    <!-- Question Review -->
    <h3 class="text-xl font-bold text-gray-900 mb-4">Detailed Review</h3>
    
    <div class="space-y-6">
        @foreach($results as $index => $result)
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="border-l-4 {{ $result['is_correct'] ? 'border-green-500' : 'border-red-500' }} p-6">
                <div class="flex justify-between items-start mb-3">
                    <span class="text-sm font-semibold {{ $result['is_correct'] ? 'text-green-600' : 'text-red-600' }}">
                        Question {{ $index + 1 }}
                    </span>
                    @if($result['is_correct'])
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                            ✓ Correct
                        </span>
                    @else
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                            ✗ Incorrect
                        </span>
                    @endif
                </div>
                
                <p class="text-gray-900 font-medium mb-4">{{ $result['question'] }}</p>
                
                <div class="space-y-2 text-sm">
                    <div class="flex items-start">
                        <span class="font-semibold text-gray-700 w-24">Your answer:</span>
                        <span class="{{ $result['is_correct'] ? 'text-green-700' : 'text-red-700' }}">
                            {{ $result['user_answer'] ? $result['user_answer'] . ') ' . ($result['options'][array_search($result['user_answer'], ['A', 'B', 'C', 'D'])] ?? 'Not answered') : 'Not answered' }}
                        </span>
                    </div>
                    
                    @if(!$result['is_correct'])
                    <div class="flex items-start">
                        <span class="font-semibold text-gray-700 w-24">Correct answer:</span>
                        <span class="text-green-700">
                            {{ $result['correct_answer'] }}) {{ $result['options'][array_search($result['correct_answer'], ['A', 'B', 'C', 'D'])] }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    
    <!-- Share / Print Button -->
    <div class="mt-8 text-center">
        <button onclick="window.print()" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
            </svg>
            Print Results
        </button>
    </div>
</div>
@endsection