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
        Schema::create('host_church_admins', function (Blueprint $table) {
            $table->id();

            $table->foreignId('host_church_id')
                ->constrained('host_churches')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->date('certified_at')->nullable();

            $table->string('status', 16)->default('active');

            $table->timestamps();

            $table->unique(['host_church_id', 'user_id']);
            $table->index(['user_id', 'status']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('host_church_admins');
    }
};
