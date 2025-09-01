
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
        Schema::create('course_user', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('users');
            $table->foreignIdFor(Course::class)->constrained()->cascadeOnDelete();
            $table->unique(['course_id', 'student_id']);
            $table->unsignedSmallInteger('progress')->default(0);
            $table->decimal('perc_progress', 4, 2)->default(0);
            $table->unsignedSmallInteger('num_of_completed_quizzes')->default(0);
            $table->timestamp('purchase_date')->default(now());
            $table->tinyInteger('rate')->nullable();
            $table->boolean('is_episodes_completed')->default(false);
            $table->boolean('is_quizzes_completed')->default(false);
            $table->boolean('get_certificate')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('course_user');
    }
};
