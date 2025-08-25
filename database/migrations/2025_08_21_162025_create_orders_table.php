<?php

use App\Enums\OrderStatusEnum;
use App\Models\Course;
use App\Models\User;
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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('users');
            $table->unsignedBigInteger('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('users');
            $table->string('order_number')->unique(); 
            $table->unsignedInteger('amount'); 
            $table->string('currency', 3)->default('usd'); 
            $table->enum('status', OrderStatusEnum::values())->default(OrderStatusEnum::PENDING);
            $table->string('payment_intent_id')->nullable()->unique(); 
            $table->foreignIdFor(Course::class)->nullable()->constrained(); 
            $table->string('course_name'); 
            $table->timestamp('purchase_date')->useCurrent();
            $table->index('status');
            $table->index('order_number');
            $table->index('payment_intent_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};