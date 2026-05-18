<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')
                  ->constrained()
                  ->onDelete('cascade');
            $table->text('question');
            $table->json('options'); // Array of 4 answer strings
            $table->string('correct_answer', 10); // "A", "B", "C", or "D"
            $table->timestamps();
            
            // Index for loading questions per quiz
            $table->index('quiz_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};