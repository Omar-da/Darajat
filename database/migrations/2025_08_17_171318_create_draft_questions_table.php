<?php

use App\Models\DraftQuiz;
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
        Schema::create('draft_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(DraftQuiz::class)->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('question_number');
            $table->unique(['draft_quiz_id', 'question_number']);
            $table->text('content');
            $table->string('answer_a');
            $table->string('answer_b');
            $table->string('answer_c');
            $table->string('answer_d');
            $table->text('explanation')->nullable();
            $table->enum('right_answer', ['a', 'b', 'c', 'd']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_questions');
    }
};
