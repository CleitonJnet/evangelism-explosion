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
        Schema::table('church_temps', function (Blueprint $table) {
            $table->string('status')->default('pending')->index();
            $table->string('normalized_name')->nullable()->index();
            $table->foreignId('resolved_church_id')->nullable()->constrained('churches')->nullOnDelete();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('church_temps', function (Blueprint $table) {
            $table->dropConstrainedForeignId('resolved_church_id');
            $table->dropConstrainedForeignId('resolved_by');
            $table->dropIndex(['status']);
            $table->dropIndex(['normalized_name']);
            $table->dropIndex(['resolved_at']);
            $table->dropColumn(['status', 'normalized_name', 'resolved_at']);
        });
    }
};
