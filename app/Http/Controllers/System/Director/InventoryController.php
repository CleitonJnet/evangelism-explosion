<?php

namespace App\Http\Controllers\System\Director;

use App\Http\Controllers\Controller;
use App\Models\Inventory;

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
            'hasActiveSimpleMaterials' => $inventory->hasActiveSimpleMaterialsWithStock(),
        ]);
    }

    public function edit(Inventory $inventory)
    {
        return view('pages.app.roles.director.inventory.edit', ['inventory' => $inventory]);
    }
}
