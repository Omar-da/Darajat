<?php

use App\Models\DraftCourse;
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
        Schema::create('draft_episodes', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('title', 100);
            $table->unsignedSmallInteger('episode_number');
            $table->unique(['course_id', 'episode_number']);
            $table->unsignedMediumInteger('duration')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('likes')->default(0);
            $table->foreignIdFor(DraftCourse::class)->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_episodes');
    }
};
