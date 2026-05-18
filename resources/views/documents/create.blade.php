@extends('layouts.app')

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-6 py-8">
            <h1 class="text-2xl font-bold text-white">Upload Lecture Notes</h1>
            <p class="text-blue-100 mt-2">Upload your PDF files to start learning with AI</p>
        </div>
        
        <form action="{{ route('documents.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            
            <!-- File Upload Area -->
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-500 transition-colors">
                <input type="file" name="pdf_file" id="pdf_file" class="hidden" accept=".pdf" required>
                <label for="pdf_file" class="cursor-pointer block">
                    <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                    </svg>
                    <p class="text-gray-600 mb-2">Click to select a PDF file</p>
                    <p class="text-gray-400 text-sm">or drag and drop</p>
                    <p class="text-gray-400 text-xs mt-4">PDF only, max 10MB</p>
                </label>
            </div>
            
            <!-- File Preview -->
            <div id="filePreview" class="hidden bg-gray-50 rounded-lg p-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3">
                        <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <p id="fileName" class="text-sm font-medium text-gray-900"></p>
                            <p id="fileSize" class="text-xs text-gray-500"></p>
                        </div>
                    </div>
                    <button type="button" onclick="clearFile()" class="text-red-600 hover:text-red-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            
            <!-- Info Box -->
            <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                <div class="flex">
                    <svg class="w-5 h-5 text-blue-600 mt-0.5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm text-blue-800">
                        <p class="font-medium mb-1">What happens after upload?</p>
                        <ul class="list-disc list-inside space-y-1 text-blue-700">
                            <li>Your PDF will be processed in the background</li>
                            <li>Text extraction and AI embedding takes a few minutes</li>
                            <li>You'll be able to chat, generate flashcards, and create quizzes once processing is complete</li>
                        </ul>
                    </div>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="flex justify-end space-x-3">
                <a href="{{ route('documents.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" id="submitBtn" disabled class="px-6 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all shadow-md disabled:opacity-50 disabled:cursor-not-allowed">
                    Upload Document
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    const fileInput = document.getElementById('pdf_file');
    const filePreview = document.getElementById('filePreview');
    const fileName = document.getElementById('fileName');
    const fileSize = document.getElementById('fileSize');
    const submitBtn = document.getElementById('submitBtn');
    
    fileInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            fileName.textContent = file.name;
            fileSize.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
            filePreview.classList.remove('hidden');
            submitBtn.disabled = false;
        } else {
            clearFile();
        }
    });
    
    function clearFile() {
        fileInput.value = '';
        filePreview.classList.add('hidden');
        submitBtn.disabled = true;
    }
</script>
@endpush
@endsection