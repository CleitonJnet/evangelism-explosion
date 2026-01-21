<?php

namespace App\Livewire\Web\Event;

use App\Models\Training;
use Livewire\Component;

class Register extends Component
{
    public Training $event;
    public bool $isPaid;

    public function mount(Training $event)
    {
        $this->event = $event;
        // ====== Flags do evento (pago x gratuito) ======
        // converte para float
        $pay = (float) preg_replace('/\D/', '', $event->payment);

        $this->isPaid = $pay > 0;

    }

    public function render()
    {
        return view('livewire.web.event.register');
    }
}
