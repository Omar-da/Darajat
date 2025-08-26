<?php

use App\Models\Course;
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
        Schema::create('episodes', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->foreignIdFor(Course::class)->constrained()->cascadeOnDelete();
            $table->string('title', 100);
            $table->unsignedSmallInteger('episode_number');
            $table->unique(['course_id', 'episode_number']);
            $table->unsignedMediumInteger('duration')->nullable();
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('likes')->default(0);
            $table->boolean('is_copied_episode')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
