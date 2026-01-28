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
        Schema::create('ojt_teams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ojt_session_id')
                ->constrained('ojt_sessions')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('mentor_id')
                ->constrained('users')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->unsignedSmallInteger('team_number');
            $table->timestamps();

            $table->unique(['ojt_session_id', 'mentor_id']);
            $table->index(['ojt_session_id', 'team_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ojt_teams');
    }
};
