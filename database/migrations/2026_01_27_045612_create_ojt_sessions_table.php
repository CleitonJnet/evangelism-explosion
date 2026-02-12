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
        Schema::create('ojt_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')
                ->constrained('trainings')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->date('date');
            $table->time('starts_at')->nullable();
            $table->time('ends_at')->nullable();
            $table->unsignedSmallInteger('week_number');
            $table->string('status', 20)->default('planned');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['training_id', 'date']);
            $table->index(['training_id', 'week_number']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ojt_sessions');
    }
};
