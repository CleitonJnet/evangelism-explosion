<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_report_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_report_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('key');
            $table->string('title')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->json('content')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['event_report_id', 'key']);
            $table->index(['event_report_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_report_sections');
    }
};
