@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-6">
        <div class="flex items-center space-x-3">
            <a href="{{ route('quiz.index', $quiz->document) }}" class="text-blue-600 hover:text-blue-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Quiz History</h1>
        </div>
        <p class="text-gray-600 mt-1">{{ $quiz->title }}</p>
    </div>
    
    <!-- Stats Summary -->
    @php
        $bestScore = $attempts->min('percentage') ? $attempts->max('percentage') : 0;
        $averageScore = $attempts->avg('percentage') ?: 0;
        $totalAttempts = $attempts->count();
    @endphp
    
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-md p-6 text-center">
            <p class="text-gray-500 text-sm">Total Attempts</p>
            <p class="text-3xl font-bold text-gray-900">{{ $totalAttempts }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6 text-center">
            <p class="text-gray-500 text-sm">Best Score</p>
            <p class="text-3xl font-bold text-green-600">{{ round($bestScore) }}%</p>
        </div>
        <div class="bg-white rounded-xl shadow-md p-6 text-center">
            <p class="text-gray-500 text-sm">Average Score</p>
            <p class="text-3xl font-bold text-blue-600">{{ round($averageScore) }}%</p>
        </div>
    </div>
    
    <!-- Attempts Table -->
    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h2 class="text-xl font-semibold text-gray-900">Attempt History</h2>
        </div>
        
        @if($attempts->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Percentage</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Result</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($attempts as $attempt)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $attempt->created_at->format('M d, Y g:i A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $attempt->score }}/{{ $attempt->total }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="flex-1 w-24 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="h-2 rounded-full 
                                            @if($attempt->percentage >= 70) bg-green-500
                                            @elseif($attempt->percentage >= 50) bg-yellow-500
                                            @else bg-red-500 @endif" 
                                            style="width: {{ $attempt->percentage }}%">
                                        </div>
                                    </div>
                                    <span class="text-sm font-medium">{{ $attempt->percentage }}%</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($attempt->passed)
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                        ✓ Passed
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                        ✗ Failed
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('quiz.result', $attempt) }}" class="text-blue-600 hover:text-blue-900">
                                    View Details →
                                </a>
                            </td>
                        \end{bmatrix}
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="px-6 py-8 text-center text-gray-500">
                <p>No attempts yet. Take the quiz to see your history!</p>
                <a href="{{ route('quiz.show', $quiz) }}" class="inline-flex items-center mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                    Take Quiz Now
                </a>
            </div>
        @endif
    </div>
</div>
@endsection