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
        Schema::create('event_dates', function (Blueprint $table) {
            $table->id();

            // FK para trainings (sua entidade “evento” atual)
            $table->foreignId('training_id')
                ->constrained('trainings')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            // Quando acontece
            $table->date('date');
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();

            // Status do dia
            $table->string('status')->default('scheduled'); // scheduled|canceled|completed

            $table->timestamps();

            // Evita duplicidade do mesmo dia/horário no mesmo training
            $table->unique(['training_id', 'date', 'start_time']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_dates');
    }
};
