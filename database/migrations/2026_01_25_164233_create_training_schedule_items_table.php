<?php

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
        Schema::create('training_schedule_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained('trainings')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('section_id')->nullable()->constrained('sections')->cascadeOnUpdate()->nullOnDelete();
            $table->date('date');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('type', 30);
            $table->string('title');
            $table->unsignedInteger('planned_duration_minutes');
            $table->unsignedInteger('suggested_duration_minutes')->nullable();
            $table->unsignedInteger('min_duration_minutes')->nullable();
            $table->string('origin', 10)->default('AUTO');
            $table->boolean('is_locked')->default(false);
            $table->string('status', 10)->default('OK');
            $table->json('conflict_reason')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['training_id', 'date', 'starts_at']);
            $table->index(['training_id', 'date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('training_schedule_items');
    }
};
