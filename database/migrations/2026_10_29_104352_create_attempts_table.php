<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('exercise_id')->constrained()->cascadeOnDelete();
            // Attempt number: 1 (first try, max 100%) or 2 (retry, max 50%).
            $table->unsignedTinyInteger('number');
            // Which class the student marked as the wrong one: correct or wrong.
            $table->string('chosen_side')->nullable();
            // The 1-based line numbers the student clicked, as a JSON array.
            $table->json('selected_lines');
            // Correctly found errors and incorrectly clicked lines.
            $table->unsignedInteger('found')->default(0);
            $table->unsignedInteger('wrong')->default(0);
            // Stars earned for this attempt (after rounding).
            $table->unsignedTinyInteger('stars')->default(0);
            $table->boolean('gave_up')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'exercise_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attempts');
    }
};
