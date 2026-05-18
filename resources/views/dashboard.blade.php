@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Welcome Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Welcome back, {{ Auth::user()->name }}! 👋</h1>
        <p class="text-gray-600 mt-2">Your AI-powered study assistant is ready to help you learn.</p>
    </div>
    
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Documents</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalDocuments }}</p>
                </div>
                <div class="bg-blue-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Flashcards</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalFlashcards }}</p>
                </div>
                <div class="bg-purple-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Total Quizzes</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $totalQuizzes }}</p>
                </div>
                <div class="bg-green-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-md p-6 border border-gray-100 hover:shadow-lg transition-shadow">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-500 text-sm font-medium">Chat Sessions</p>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ $recentChats->count() }}+</p>
                </div>
                <div class="bg-yellow-100 rounded-full p-3">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Recent Chat Sessions -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-purple-50">
                <h2 class="text-xl font-semibold text-gray-900">Recent Conversations</h2>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentChats as $chat)
                <a href="{{ route('chat.show', $chat['id']) }}" class="block hover:bg-gray-50 transition-colors">
                    <div class="px-6 py-4">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">{{ $chat['title'] }}</p>
                                <p class="text-xs text-gray-500 mt-1">Document: {{ $chat['document_name'] }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-gray-400">{{ $chat['created_at']->diffForHumans() }}</p>
                                <span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium text-blue-700 bg-blue-100 mt-1">
                                    Continue
                                </span>
                            </div>
                        </div>
                    </div>
                </a>
                @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    <p>No chat sessions yet. Start a conversation with your notes!</p>
                </div>
                @endforelse
            </div>
        </div>
        
        <!-- Recent Quiz Attempts -->
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-teal-50">
                <h2 class="text-xl font-semibold text-gray-900">Recent Quiz Results</h2>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($recentQuizAttempts as $attempt)
                <div class="px-6 py-4">
                    <div class="flex justify-between items-center">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">{{ $attempt['quiz_title'] }}</p>
                            <p class="text-xs text-gray-500 mt-1">Document: {{ $attempt['document_name'] }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-bold {{ $attempt['passed'] ? 'text-green-600' : 'text-red-600' }}">
                                {{ $attempt['score'] }}/{{ $attempt['total'] }}
                                ({{ $attempt['percentage'] }}%)
                            </p>
                            <p class="text-xs text-gray-400 mt-1">{{ $attempt['created_at']->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-gray-500">
                    <p>No quiz attempts yet. Generate a quiz to test your knowledge!</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>
    
    <!-- Recent Documents -->
    <div class="mt-8 bg-white rounded-xl shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
            <h2 class="text-xl font-semibold text-gray-900">My Documents</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Filename</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uploaded</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($documents as $doc)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $doc['filename'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($doc['status'] == 'pending')
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                            @elseif($doc['status'] == 'processing')
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Processing</span>
                            @elseif($doc['status'] == 'processed')
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Processed</span>
                            @else
                                <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $doc['created_at']->diffForHumans() }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            @if($doc['is_processed'])
                                <a href="{{ route('chat.index', $doc['id']) }}" class="text-blue-600 hover:text-blue-900 mr-3">Chat</a>
                                <a href="{{ route('flashcards.index', $doc['id']) }}" class="text-purple-600 hover:text-purple-900 mr-3">Flashcards</a>
                                <a href="{{ route('quiz.index', $doc['id']) }}" class="text-green-600 hover:text-green-900">Quiz</a>
                            @else
                                <span class="text-gray-400">Processing...</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                            No documents uploaded yet. Click "Upload Document" to get started!
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection