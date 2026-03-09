<?php

namespace App\Http\Controllers\System\Director;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Material;

class InventoryController extends Controller
{
    public function index()
    {
        return view('pages.app.roles.director.inventory.index');
    }

    public function create()
    {
        return view('pages.app.roles.director.inventory.create');
    }

    public function show(Inventory $inventory)
    {
        return view('pages.app.roles.director.inventory.show', [
            'inventory' => $inventory,
            'hasActiveSimpleMaterials' => Material::query()
                ->where('type', 'simple')
                ->where('is_active', true)
                ->exists(),
        ]);
    }

    public function edit(Inventory $inventory)
    {
        return view('pages.app.roles.director.inventory.edit', ['inventory' => $inventory]);
    }
}
