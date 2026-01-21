<?php

namespace App\Livewire\Pages\App\Director\Church;

use App\Models\Church;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.pages.app.director.church.index', [
            'churches' => Church::orderBy('name')->paginate(10),
        ]);
    }
}
