<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_material_id')->constrained('materials')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignId('component_material_id')->constrained('materials')->cascadeOnUpdate()->restrictOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();

            $table->unique(['parent_material_id', 'component_material_id'], 'mat_comp_parent_component_unique');
        });

        if (in_array(Schema::getConnection()->getDriverName(), ['pgsql'], true)) {
            DB::statement('ALTER TABLE material_components ADD CONSTRAINT material_components_parent_component_different CHECK (parent_material_id <> component_material_id)');
        }

        if (in_array(Schema::getConnection()->getDriverName(), ['mysql', 'mariadb', 'pgsql'], true)) {
            DB::statement('ALTER TABLE material_components ADD CONSTRAINT material_components_quantity_positive CHECK (quantity > 0)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('material_components');
    }
};
