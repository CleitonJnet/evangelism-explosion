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
        Schema::create('ojt_training_mentors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')
                ->constrained('trainings')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('mentor_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('status', 16)->default('active');
            $table->timestamps();

            $table->unique(['training_id', 'mentor_id']);
            $table->index(['mentor_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ojt_training_mentors');
    }
};
