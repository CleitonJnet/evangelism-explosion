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
        Schema::create('ojt_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ojt_team_id')
                ->constrained('ojt_teams')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->string('contact_type', 30)->nullable();
            $table->unsignedSmallInteger('gospel_presentations')->default(0);
            $table->unsignedSmallInteger('listeners_count')->default(0);
            $table->unsignedSmallInteger('results_decisions')->default(0);
            $table->unsignedSmallInteger('results_interested')->default(0);
            $table->unsignedSmallInteger('results_rejection')->default(0);
            $table->unsignedSmallInteger('results_assurance')->default(0);
            $table->boolean('follow_up_scheduled')->default(false);
            $table->json('outline_participation')->nullable();
            $table->text('lesson_learned')->nullable();
            $table->json('public_report')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['ojt_team_id']);
            $table->index(['submitted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ojt_reports');
    }
};
