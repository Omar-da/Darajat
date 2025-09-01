<?php

use App\Models\DraftEpisode;
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
        Schema::create('draft_quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(DraftEpisode::class)->unique()->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('num_of_questions');
            $table->timestamp('quiz_writing_date')->default(now());
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_quizzes');
    }
};
