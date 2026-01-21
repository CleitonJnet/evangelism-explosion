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
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('banner')->nullable();
            $table->integer('order')->nullable();
            $table->string('duration')->nullable();
            $table->string('devotional')->nullable();
            $table->text('description')->nullable();
            $table->text('knowhow')->nullable();
            $table->timestamps();

            $table->foreignId('course_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};
