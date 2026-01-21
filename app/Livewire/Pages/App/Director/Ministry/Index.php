<?php

namespace App\Livewire\Pages\App\Director\Ministry;

use App\Models\Ministry;
use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.pages.app.director.ministry.index', [
            'ministries' => Ministry::orderBy('id','desc')->paginate(10),
        ]);
    }
}
