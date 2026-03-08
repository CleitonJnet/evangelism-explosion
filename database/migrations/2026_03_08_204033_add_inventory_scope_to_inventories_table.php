<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->string('kind')->default('teacher')->after('name');
            $table->foreignId('user_id')->nullable()->after('email')->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->boolean('is_active')->default(true)->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['kind', 'is_active']);
        });
    }
};
