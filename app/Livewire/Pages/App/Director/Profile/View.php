<?php

namespace App\Livewire\Pages\App\Director\Profile;

use App\Models\User;
use Livewire\Component;

class View extends Component
{
    public $profile;

    public function mount(User $profile)
    {
        $this->profile = $profile;
    }

    public function render()
    {
        return view('livewire.pages.app.director.profile.view');
    }
}
