<?php

use App\Livewire\Pages\App\Teacher\Training\CreateChurchModal;
use App\Models\Church;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('creates a church and exposes it as the selected church', function () {
    Livewire::test(CreateChurchModal::class)
        ->set('showModal', true)
        ->set('church_name', 'Igreja Esperança')
        ->set('pastor_name', 'Pr. João Silva')
        ->set('phone_church', '(21) 99999-0000')
        ->set('church_email', 'igreja@example.com')
        ->set('church_contact', 'Maria Souza')
        ->set('church_contact_phone', '(21) 98888-0000')
        ->set('church_contact_email', 'contato@example.com')
        ->set('church_notes', 'Próxima ao centro da cidade.')
        ->set('churchAddress.postal_code', '24000000')
        ->set('churchAddress.street', 'Rua das Flores')
        ->set('churchAddress.number', '123')
        ->set('churchAddress.complement', 'Salão principal')
        ->set('churchAddress.district', 'Centro')
        ->set('churchAddress.city', 'Niterói')
        ->set('churchAddress.state', 'rj')
        ->call('submit')
        ->assertSet('showModal', false)
        ->assertSet('selectedChurch.name', 'Igreja Esperança');

    $church = Church::query()->where('name', 'Igreja Esperança')->first();

    expect($church)->not->toBeNull();
    expect($church?->state)->toBe('RJ');
});
