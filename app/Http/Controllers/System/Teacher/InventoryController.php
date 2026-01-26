<?php

namespace App\Http\Controllers\System\Teacher;

use App\Http\Controllers\Controller;

class InventoryController extends Controller
{
    public function index()
    {
        return view("pages.app.roles.teacher.inventory.index");
    }

    public function create()
    {
        return view("pages.app.roles.teacher.inventory.create");
    }

    public function show(string $inventory)
    {
        return view("pages.app.roles.teacher.inventory.show", ['inventory'=>$inventory]);
    }


    public function edit(string $inventory)
    {
        return view("pages.app.roles.teacher.inventory.edit", ['inventory'=>$inventory]);
    }
}
