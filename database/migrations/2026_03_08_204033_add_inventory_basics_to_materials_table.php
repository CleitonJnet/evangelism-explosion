<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->string('type')->default('simple')->after('name');
            $table->unsignedInteger('minimum_stock')->default(0)->after('price');
            $table->boolean('is_active')->default(true)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['type', 'minimum_stock', 'is_active']);
        });
    }
};
