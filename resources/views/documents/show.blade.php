@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center space-x-3">
            <a href="{{ route('documents.index') }}" class="text-blue-600 hover:text-blue-800">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ $document->filename }}</h1>
        </div>
        <p class="text-gray-600 mt-2">Study tools for this document</p>
    </div>
    
    <!-- Status Card -->
    <div class="bg-white rounded-xl shadow-md p-6 mb-8">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-gray-500">Document Status</p>
                @if($document->status == 'pending')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-yellow-100 text-yellow-800">
                        <svg class="w-4 h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Pending Processing
                    </span>
                @elseif($document->status == 'processing')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                        <svg class="w-4 h-4 mr-1 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Processing...
                    </span>
                @elseif($document->status == 'processed')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Ready to Use
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-red-100 text-red-800">
                        Processing Failed
                    </span>
                @endif
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">Uploaded</p>
                <p class="text-sm font-medium text-gray-900">{{ $document->created_at->format('M d, Y') }}</p>
            </div>
        </div>
        
        @if($document->status == 'failed')
            <div class="mt-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                <p class="text-sm text-red-700">Document processing failed. Please try uploading again or check the file format.</p>
            </div>
        @endif
    </div>
    
    <!-- Study Tools Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Chat Tool -->
        <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-2">AI Chat</h3>
                <p class="text-gray-600 mb-4">Ask questions and get answers based solely on your notes</p>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">{{ $chatSessionCount }} conversations</span>
                    @if($document->isProcessed())
                        <a href="{{ route('chat.index', $document) }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium">
                            Start Chatting
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    @else
                        <span class="text-gray-400 text-sm">Processing required</span>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Flashcards Tool -->
        <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                </svg>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Flashcards</h3>
                <p class="text-gray-600 mb-4">Generate study cards to memorize key concepts</p>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">{{ $flashcardCount }} cards</span>
                    @if($document->isProcessed())
                        <a href="{{ route('flashcards.index', $document) }}" class="inline-flex items-center text-purple-600 hover:text-purple-800 font-medium">
                            {{ $flashcardCount > 0 ? 'Study Now' : 'Generate' }}
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    @else
                        <span class="text-gray-400 text-sm">Processing required</span>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Quiz Tool -->
        <div class="bg-white rounded-xl shadow-md hover:shadow-xl transition-all duration-300 overflow-hidden group">
            <div class="bg-gradient-to-r from-green-500 to-green-600 p-6">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-2">Quizzes</h3>
                <p class="text-gray-600 mb-4">Test your knowledge with auto-generated questions</p>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">{{ $quizCount }} quizzes</span>
                    @if($document->isProcessed())
                        <a href="{{ route('quiz.index', $document) }}" class="inline-flex items-center text-green-600 hover:text-green-800 font-medium">
                            {{ $quizCount > 0 ? 'Take Quiz' : 'Generate' }}
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                        </a>
                    @else
                        <span class="text-gray-400 text-sm">Processing required</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection