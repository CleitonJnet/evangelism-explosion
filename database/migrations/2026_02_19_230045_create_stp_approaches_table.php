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
        Schema::create('stp_approaches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('training_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('stp_session_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('stp_team_id')->nullable()->constrained()->cascadeOnUpdate()->nullOnDelete();
            $table->string('type');
            $table->string('status');
            $table->integer('position')->default(0);

            $table->string('person_name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('street')->nullable();
            $table->string('number')->nullable();
            $table->string('complement')->nullable();
            $table->string('district')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('reference_point')->nullable();

            $table->unsignedSmallInteger('gospel_explained_times')->nullable();
            $table->unsignedSmallInteger('people_count')->nullable();
            $table->string('result')->nullable();
            $table->boolean('means_growth')->default(false);
            $table->dateTime('follow_up_scheduled_at')->nullable();

            $table->text('public_q2_answer')->nullable();
            $table->text('public_lesson')->nullable();

            $table->foreignId('created_by_user_id')->constrained('users')->cascadeOnUpdate();
            $table->foreignId('reported_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->foreignId('reviewed_by_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->dateTime('reviewed_at')->nullable();

            $table->json('payload')->nullable();
            $table->timestamps();

            $table->index('training_id');
            $table->index('stp_session_id');
            $table->index('stp_team_id');
            $table->index('type');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stp_approaches');
    }
};
