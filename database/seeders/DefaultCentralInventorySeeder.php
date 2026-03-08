<?php

namespace Database\Seeders;

use App\Models\Inventory;
use Illuminate\Database\Seeder;

class DefaultCentralInventorySeeder extends Seeder
{
    public function run(): void
    {
        $hasCentralInventory = Inventory::query()
            ->where('kind', 'central')
            ->exists();

        if ($hasCentralInventory) {
            return;
        }

        Inventory::query()->create([
            'name' => 'Estoque Central',
            'kind' => 'central',
            'is_active' => true,
            'notes' => 'Estoque central inicial criado automaticamente pelo seeder.',
        ]);
    }
}
