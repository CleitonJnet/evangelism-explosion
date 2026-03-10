<?php

namespace App\Livewire\Pages\App\Director\Church;

use App\Services\HostChurchReadModel;
use Illuminate\View\View;
use Livewire\Component;

class Hosts extends Component
{
    public function render(): View
    {
        return view('livewire.pages.app.director.church.hosts', [
            'hosts' => app(HostChurchReadModel::class)
                ->registeredHostsQuery()
                ->paginate(10),
        ]);
    }
}
