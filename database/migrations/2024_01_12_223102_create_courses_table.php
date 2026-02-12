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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('order')->nullable();
            $table->integer('execution')->default(0)->nullable();
            $table->string('type')->nullable();
            $table->string('initials')->nullable();
            $table->string('name')->nullable();
            $table->string('slogan')->nullable();
            $table->string('targetAudience')->nullable();
            $table->string('learnMoreLink')->nullable();
            $table->string('certificate')->nullable();
            $table->string('color')->default('#4F4F4F')->nullable();
            $table->text('description')->nullable();
            $table->text('knowhow')->nullable();
            $table->string('logo')->nullable();
            $table->string('banner')->nullable();
            $table->string('price')->default('0,00');
            $table->timestamps();

            $table->foreignId('ministry_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
