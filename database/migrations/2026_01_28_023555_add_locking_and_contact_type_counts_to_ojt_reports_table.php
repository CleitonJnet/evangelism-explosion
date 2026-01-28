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
        Schema::table('ojt_reports', function (Blueprint $table) {
            $table->json('contact_type_counts')->nullable()->after('contact_type');
            $table->boolean('is_locked')->default(false)->after('submitted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ojt_reports', function (Blueprint $table) {
            $table->dropColumn(['contact_type_counts', 'is_locked']);
        });
    }
};
