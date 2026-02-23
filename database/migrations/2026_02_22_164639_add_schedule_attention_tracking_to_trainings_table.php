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
        Schema::table('trainings', function (Blueprint $table) {
            $table->timestamp('schedule_attention_shown_at')->nullable()->after('schedule_settings');
            $table->timestamp('schedule_adjusted_at')->nullable()->after('schedule_attention_shown_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn(['schedule_attention_shown_at', 'schedule_adjusted_at']);
        });
    }
};
