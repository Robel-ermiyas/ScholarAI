@extends('layouts.app')

@section('content')
<div class="flex flex-col h-screen bg-gray-100">
    <!-- Chat Header -->
    <div class="bg-white shadow-md border-b border-gray-200 py-4">
        <div class="max-w-4xl mx-auto px-4 flex justify-between items-center">
            <div class="flex items-center space-x-4">
                <a href="{{ route('chat.index', $session->document) }}" class="text-gray-600 hover:text-blue-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h2 class="text-lg font-semibold text-gray-800">{{ $session->title }}</h2>
                    <p class="text-xs text-gray-500">Document: {{ $session->document->filename }}</p>
                </div>
            </div>
            <form action="{{ route('chat.destroy', $session) }}" method="POST" onsubmit="return confirm('Delete this conversation?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:text-red-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </form>
        </div>
    </div>
    
    <!-- Messages Container -->
    <div class="flex-1 overflow-y-auto py-6 pb-24" id="messagesContainer">
        <div class="max-w-4xl mx-auto px-4 space-y-4" id="messagesList">
            @foreach($messages as $message)
                <div class="message-animation {{ $message->role === 'user' ? 'flex justify-end' : 'flex justify-start' }}">
                    <div class="max-w-[70%] {{ $message->role === 'user' 
                        ? 'bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-l-2xl rounded-tr-2xl' 
                        : 'bg-white text-gray-800 rounded-r-2xl rounded-tl-2xl shadow-md' }} p-4">
                        <div class="flex items-start space-x-2">
                            @if($message->role === 'assistant')
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center">
                                        <span class="text-white text-xs font-bold">AI</span>
                                    </div>
                                </div>
                            @endif
                            <div class="flex-1">
                                <p class="text-sm whitespace-pre-wrap">{{ $message->content }}</p>
                                <p class="text-xs mt-1 opacity-75">{{ $message->created_at->format('g:i A') }}</p>
                            </div>
                            @if($message->role === 'user')
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-white bg-opacity-30 flex items-center justify-center">
                                        <span class="text-white text-xs font-bold">U</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Loading Indicator -->
        <div id="loadingIndicator" class="max-w-4xl mx-auto px-4 hidden">
            <div class="flex justify-start">
                <div class="bg-white text-gray-800 rounded-r-2xl rounded-tl-2xl shadow-md p-4">
                    <div class="flex items-center space-x-3">
                        <div class="loader"></div>
                        <p class="text-sm text-gray-500">ScholarAI is thinking...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Input Area -->
    <div class="bg-white border-t border-gray-200 py-4">
        <div class="max-w-4xl mx-auto px-4">
            <form id="chatForm" class="flex space-x-3">
                @csrf
                <div class="flex-1 relative">
                    <textarea id="messageInput" rows="1" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none" placeholder="Ask a question about your notes..."></textarea>
                </div>
                <button type="submit" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all shadow-md disabled:opacity-50 disabled:cursor-not-allowed" id="sendButton">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                </button>
            </form>
            <p class="text-xs text-gray-400 mt-2 text-center">ScholarAI answers only from your uploaded notes</p>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const messagesContainer = document.getElementById('messagesContainer');
    const messagesList = document.getElementById('messagesList');
    const chatForm = document.getElementById('chatForm');
    const messageInput = document.getElementById('messageInput');
    const sendButton = document.getElementById('sendButton');
    const loadingIndicator = document.getElementById('loadingIndicator');
    
    // Auto-scroll to bottom
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }
    // Ensure we scroll and focus after layout is settled
    window.addEventListener('load', function() {
        scrollToBottom();
        if (messageInput) messageInput.focus();
    });
    // Fallback: after DOM ready, try again shortly
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() { scrollToBottom(); }, 50);
    });
    // Also keep footer visible on resize/orientation change
    window.addEventListener('resize', function() { scrollToBottom(); });
    
    // Auto-resize textarea
    messageInput.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 150) + 'px';
    });
    
    // Handle form submission
    chatForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const message = messageInput.value.trim();
        if (!message) return;
        
        // Disable input and button
        messageInput.disabled = true;
        sendButton.disabled = true;
        
        // Add user message to chat
        const userMessageHtml = `
            <div class="message-animation flex justify-end">
                <div class="max-w-[70%] bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-l-2xl rounded-tr-2xl p-4">
                    <div class="flex items-start space-x-2">
                        <div class="flex-1">
                            <p class="text-sm whitespace-pre-wrap">${escapeHtml(message)}</p>
                            <p class="text-xs mt-1 opacity-75">Just now</p>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full bg-white bg-opacity-30 flex items-center justify-center">
                                <span class="text-white text-xs font-bold">U</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `;
        messagesList.insertAdjacentHTML('beforeend', userMessageHtml);
        messageInput.value = '';
        messageInput.style.height = 'auto';
        scrollToBottom();
        
        // Show loading indicator
        loadingIndicator.classList.remove('hidden');
        scrollToBottom();
        
        try {
            // Send request to AI
            const response = await fetch('{{ route("chat.ask", $session) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ message: message })
            });
            
            const data = await response.json();
            
            // Remove loading indicator
            loadingIndicator.classList.add('hidden');
            
            if (data.success) {
                // Add AI response to chat
                const aiMessageHtml = `
                    <div class="message-animation flex justify-start">
                        <div class="max-w-[70%] bg-white text-gray-800 rounded-r-2xl rounded-tl-2xl shadow-md p-4">
                            <div class="flex items-start space-x-2">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-purple-500 flex items-center justify-center">
                                        <span class="text-white text-xs font-bold">AI</span>
                                    </div>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm whitespace-pre-wrap">${escapeHtml(data.message)}</p>
                                    <p class="text-xs mt-1 text-gray-400">Just now</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                messagesList.insertAdjacentHTML('beforeend', aiMessageHtml);
                scrollToBottom();
            } else {
                // Show error message
                const errorHtml = `
                    <div class="flex justify-start">
                        <div class="max-w-[70%] bg-red-100 text-red-800 rounded-r-2xl rounded-tl-2xl p-4">
                            <p class="text-sm">Error: ${escapeHtml(data.error)}</p>
                        </div>
                    </div>
                `;
                messagesList.insertAdjacentHTML('beforeend', errorHtml);
                scrollToBottom();
            }
        } catch (error) {
            loadingIndicator.classList.add('hidden');
            const errorHtml = `
                <div class="flex justify-start">
                    <div class="max-w-[70%] bg-red-100 text-red-800 rounded-r-2xl rounded-tl-2xl p-4">
                        <p class="text-sm">Network error. Please try again.</p>
                    </div>
                </div>
            `;
            messagesList.insertAdjacentHTML('beforeend', errorHtml);
            scrollToBottom();
        } finally {
            // Re-enable input and button
            messageInput.disabled = false;
            sendButton.disabled = false;
            messageInput.focus();
        }
    });
    
    // Escape HTML to prevent XSS
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
@endpush
@endsection