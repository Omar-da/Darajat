<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('platform_statistics', function (Blueprint $table) {
            $table->id();
            $table->integer('num_of_courses')->default(0);
            $table->integer('num_of_students')->default(0);
            $table->integer('num_of_teachers')->default(0);
            $table->integer('num_of_countries')->default(0);
            $table->integer('num_of_topics')->default(0);
            $table->integer('num_of_views')->default(0);
            $table->decimal('commission', 15, 2)->default(0);
            $table->decimal('total_profit', 15, 2)->default(0);
            $table->timestamps();
        });

        // Insert the initial row
        DB::table('platform_statistics')->insert(['id' => 1]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_statistics');
    }
};
