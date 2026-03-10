<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Inventory::class);

        return view('pages.app.roles.teacher.inventory.index');
    }

    public function show(Inventory $inventory): View
    {
        $this->authorize('view', $inventory);

        return view('pages.app.roles.teacher.inventory.show', ['inventory' => $inventory]);
    }

    public function edit(Inventory $inventory): View
    {
        $this->authorize('update', $inventory);

        return view('pages.app.roles.teacher.inventory.edit', ['inventory' => $inventory]);
    }
}
