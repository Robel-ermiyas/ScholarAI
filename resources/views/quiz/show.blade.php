@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-3 mb-2">
            <a href="{{ route('quiz.index', $quiz->document) }}" class="text-blue-600 hover:text-blue-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ $quiz->title }}</h1>
        </div>
        <p class="text-gray-600">{{ $quiz->document->filename }}</p>
        @if($previousAttempt)
            <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-sm bg-blue-100 text-blue-800">
                Previous score: {{ $previousAttempt->score }}/{{ $previousAttempt->total }} ({{ $previousAttempt->percentage }}%)
            </div>
        @endif
    </div>
    
    <!-- Quiz Form -->
    <form action="{{ route('quiz.submit', $quiz) }}" method="POST" id="quizForm">
        @csrf
        
        @foreach($questions as $index => $question)
        <div class="bg-white rounded-xl shadow-md p-6 mb-6">
            <div class="mb-4">
                <span class="text-sm font-semibold text-blue-600">Question {{ $index + 1 }} of {{ $questions->count() }}</span>
                <h3 class="text-lg font-medium text-gray-900 mt-2">{{ $question->question }}</h3>
            </div>
            
            <div class="space-y-3">
                @php
                    $options = $question->options;
                    $letters = ['A', 'B', 'C', 'D'];
                @endphp
                
                @foreach($options as $optIndex => $option)
                    @php $letter = $letters[$optIndex]; @endphp
                    <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer transition-all">
                        <input type="radio" name="answers[{{ $index }}]" value="{{ $letter }}" class="w-4 h-4 text-blue-600 focus:ring-blue-500" required>
                        <span class="ml-3">
                            <strong class="text-gray-800">{{ $letter }})</strong>
                            <span class="text-gray-700 ml-2">{{ $option }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
        @endforeach
        
        <!-- Submit Button -->
        <div class="sticky bottom-0 bg-white border-t border-gray-200 py-4 -mx-4 px-4 shadow-lg">
            <div class="max-w-4xl mx-auto">
                <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg hover:from-green-600 hover:to-green-700 transition-all shadow-md font-semibold">
                    Submit Quiz
                </button>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Add warning before leaving page with unsaved answers
    let formChanged = false;
    const radioButtons = document.querySelectorAll('input[type="radio"]');
    
    radioButtons.forEach(radio => {
        radio.addEventListener('change', () => {
            formChanged = true;
        });
    });
    
    window.addEventListener('beforeunload', (e) => {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = 'You have unsaved answers. Are you sure you want to leave?';
        }
    });
    
    document.getElementById('quizForm').addEventListener('submit', () => {
        formChanged = false;
    });
</script>
@endpush
@endsection