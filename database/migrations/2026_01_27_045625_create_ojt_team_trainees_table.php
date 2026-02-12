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
        Schema::create('ojt_team_trainees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ojt_team_id')
                ->constrained('ojt_teams')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('trainee_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('order');
            $table->timestamps();

            $table->unique(['ojt_team_id', 'trainee_id']);
            $table->index(['trainee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ojt_team_trainees');
    }
};
