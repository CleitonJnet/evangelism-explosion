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
            $table->unsignedSmallInteger('ojt_count_override')->nullable()->after('welcome_duration_minutes');
            $table->string('ojt_policy_override', 10)->nullable()->after('ojt_count_override');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->dropColumn(['ojt_count_override', 'ojt_policy_override']);
        });
    }
};
