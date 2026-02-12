<?php

namespace App\Http\Controllers\System\Director;

use App\Http\Controllers\Controller;

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

    public function show(string $inventory)
    {
        return view('pages.app.roles.director.inventory.show', ['inventory' => $inventory]);
    }

    public function edit(string $inventory)
    {
        return view('pages.app.roles.director.inventory.edit', ['inventory' => $inventory]);
    }
}
