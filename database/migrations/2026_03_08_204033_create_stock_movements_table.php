<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('material_id')->constrained()->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('training_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->string('movement_type');
            $table->unsignedInteger('quantity');
            $table->integer('balance_after')->nullable();
            $table->uuid('batch_uuid')->nullable();
            $table->text('notes')->nullable();
            $table->nullableMorphs('reference');
            $table->timestamps();

            $table->index(['inventory_id', 'material_id'], 'stock_movements_inventory_material_idx');
            $table->index('movement_type', 'stock_movements_type_idx');
            $table->index('batch_uuid', 'stock_movements_batch_idx');
        });

        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb', 'pgsql'], true)) {
            DB::statement('ALTER TABLE stock_movements ADD CONSTRAINT stock_movements_quantity_positive CHECK (quantity > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
