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
            $table->dropColumn([
                'totStudents',
                'totChurches',
                'totPastors',
                'totKitsUsed',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trainings', function (Blueprint $table) {
            $table->integer('totStudents')->default(0);
            $table->integer('totChurches')->default(0);
            $table->integer('totPastors')->default(0);
            $table->integer('totKitsUsed')->default(0);
        });
    }
};
