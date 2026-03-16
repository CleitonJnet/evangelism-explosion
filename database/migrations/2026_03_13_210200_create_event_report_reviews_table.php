<?php

use App\Enums\EventReportReviewOutcome;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_report_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_report_id')->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('reviewer_user_id')->nullable()->constrained('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('outcome')->default(EventReportReviewOutcome::Commented->value);
            $table->text('comment')->nullable();
            $table->json('payload')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['event_report_id', 'outcome']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('event_report_reviews');
    }
};
