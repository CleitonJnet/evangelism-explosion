<?php

namespace App\Livewire\Pages\App\Director\Church;

use App\Models\HostChurch;
use Illuminate\View\View;
use Livewire\Component;

class Hosts extends Component
{
    public function render(): View
    {
        return view('livewire.pages.app.director.church.hosts', [
            'hosts' => HostChurch::query()
                ->join('churches', 'host_churches.church_id', '=', 'churches.id')
                ->orderBy('churches.name')
                ->select('host_churches.*')
                ->with('church')
                ->paginate(10),
        ]);
    }
}
