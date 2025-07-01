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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Course::class)->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('student_id');
            $table->foreign('student_id')->references('id')->on('users');
            $table->unsignedBigInteger('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('users');
            $table->decimal('amount', 10, 2); // e.g., 100.00
            $table->decimal('platform_fee', 10, 2); // e.g., 10.00 (10% of amount)
            $table->string('currency')->default('usd');
            $table->string('stripe_payment_id'); // Stripe PaymentIntent ID
            $table->string('stripe_charge_id')->nullable(); // Stripe Charge ID
            $table->string('status'); // 'succeeded', 'failed', 'pending'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
