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
        Schema::create('inventory_material', function (Blueprint $table) {
            $table->foreignId('material_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('inventory_id')->nullable()->constrained()->cascadeOnUpdate()->cascadeOnDelete();

            $table->integer('received_items')->default(0);
            $table->integer('current_quantity')->default(0);
            $table->integer('lost_items')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_material');
    }
};
