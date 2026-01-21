<?php

namespace App\Livewire\Pages\App\Director\Ministry;

use App\Models\Ministry;
use Livewire\Component;

class View extends Component
{
    public $ministry;
    
    public function mount(Ministry $ministry) {
        $this->ministry = $ministry;
    }

    public function render()
    {
        return view('livewire.pages.app.director.ministry.view', [
            'launcher' => $this->ministry->courses()->where('execution',0)->orderBy('order')->paginate(10),
            'implementation' => $this->ministry->courses()->where('execution',1)->orderBy('order')->paginate(10),
        ]);
    }
}
