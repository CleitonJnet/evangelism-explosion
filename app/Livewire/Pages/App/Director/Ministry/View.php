<?php

namespace App\Livewire\Pages\App\Director\Ministry;

use App\Models\Ministry;
use Illuminate\View\View as ViewContract;
use Livewire\Attributes\On;
use Livewire\Component;

class View extends Component
{
    public Ministry $ministry;

    public function mount(Ministry $ministry): void
    {
        $this->ministry = $ministry;
    }

    #[On('director-ministry-updated')]
    public function refreshMinistry(int $ministryId): void
    {
        if ($this->ministry->id !== $ministryId) {
            return;
        }

        $this->ministry = $this->ministry->fresh();
    }

    public function render(): ViewContract
    {
        return view('livewire.pages.app.director.ministry.view', [
            'launcher' => $this->ministry->courses()->where('execution', 0)->orderBy('order')->paginate(10),
            'implementation' => $this->ministry->courses()->where('execution', 1)->orderBy('order')->paginate(10),
        ]);
    }
}
