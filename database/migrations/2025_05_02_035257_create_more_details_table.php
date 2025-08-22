<?php

use App\Enums\EducationEnum;
use App\Models\Country;
use App\Models\JobTitle;
use App\Models\Speciality;
use App\Models\University;
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
        Schema::create('more_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('stripe_customer_id')->nullable()->unique(   );
            $table->foreignIdFor(JobTitle::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Country::class)->nullable()->constrained();
            $table->string('linked_in_url', )->nullable();
            $table->enum('education', EducationEnum::values())->default(EducationEnum::NONE);
            $table->foreignIdFor(University::class)->nullable()->constrained();
            $table->foreignIdFor(Speciality::class)->nullable()->constrained();
            $table->text('work_experience', 1000)->nullable();
            $table->boolean('is_banned')->default(false);
            $table->boolean('is_active_today')->default(false);
            $table->unsignedTinyInteger('num_of_inactive_days')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('more_details');
    }
};
