<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $this->authorize('viewAny', Inventory::class);

        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        $inventories = $user->inventories()
            ->select('id')
            ->orderBy('name')
            ->get();

        if ($inventories->count() === 1) {
            return redirect()->route('app.teacher.inventory.show', ['inventory' => $inventories->first()->id]);
        }

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
