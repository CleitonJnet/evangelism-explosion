<?php

namespace App\Livewire\Pages\App\Director\Church;

use App\Models\Church;
use Livewire\Component;

class View extends Component
{
    public $church;
    
    public function mount(Church $church) {
        $this->church = $church;
    }

    public function render()
    {
        return view('livewire.pages.app.director.church.view', [
            'profiles' => $this->church->members()->orderBy('name')->paginate(10),
        ]);
    }
}
