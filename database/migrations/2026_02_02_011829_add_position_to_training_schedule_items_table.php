<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('training_schedule_items', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0);

            $table->index(['training_id', 'date', 'position'], 'training_schedule_items_position_index');
        });

        $groups = DB::table('training_schedule_items')
            ->select('training_id', 'date')
            ->distinct()
            ->get();

        foreach ($groups as $group) {
            $items = DB::table('training_schedule_items')
                ->where('training_id', $group->training_id)
                ->where('date', $group->date)
                ->orderBy('starts_at')
                ->orderBy('id')
                ->get(['id']);

            $position = 1;

            foreach ($items as $item) {
                DB::table('training_schedule_items')
                    ->where('id', $item->id)
                    ->update(['position' => $position]);
                $position++;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('training_schedule_items', function (Blueprint $table) {
            $table->dropIndex('training_schedule_items_position_index');
            $table->dropColumn('position');
        });
    }
};
