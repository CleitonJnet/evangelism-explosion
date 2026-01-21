<?php

use App\Livewire\Pages\App\Director\Church\Create;
use App\Models\Church;
use Livewire\Livewire;

test('church creation validates required fields', function () {
    Livewire::test(Create::class)
        ->call('submit')
        ->assertHasErrors([
            'church_name' => 'required',
            'pastor_name' => 'required',
            'phone_church' => 'required',
            'church_contact' => 'required',
            'church_contact_phone' => 'required',
            'church_contact_email' => 'required',
            'church_notes' => 'required',
            'churchAddress.postal_code' => 'required',
            'churchAddress.street' => 'required',
            'churchAddress.number' => 'required',
            'churchAddress.district' => 'required',
            'churchAddress.city' => 'required',
            'churchAddress.state' => 'required',
        ]);
});

test('church creation validates email fields', function () {
    Livewire::test(Create::class)
        ->set('church_name', 'Igreja Central')
        ->set('pastor_name', 'Joao Silva')
        ->set('phone_church', '11999999999')
        ->set('church_contact', 'Maria Souza')
        ->set('church_contact_phone', '11888888888')
        ->set('church_contact_email', 'email-invalido')
        ->set('church_notes', 'Comentario qualquer')
        ->set('churchAddress.postal_code', '01001-000')
        ->set('churchAddress.street', 'Rua Exemplo')
        ->set('churchAddress.number', '100')
        ->set('churchAddress.district', 'Centro')
        ->set('churchAddress.city', 'Sao Paulo')
        ->set('churchAddress.state', 'SP')
        ->call('submit')
        ->assertHasErrors(['church_contact_email' => 'email']);
});

test('church can be created', function () {
    Livewire::test(Create::class)
        ->set('church_name', 'Igreja Central')
        ->set('pastor_name', 'Joao Silva')
        ->set('phone_church', '11999999999')
        ->set('church_email', 'contato@igreja.com')
        ->set('church_contact', 'Maria Souza')
        ->set('church_contact_phone', '11888888888')
        ->set('church_contact_email', 'maria@igreja.com')
        ->set('church_notes', 'Comentario qualquer')
        ->set('churchAddress.postal_code', '01001-000')
        ->set('churchAddress.street', 'Rua Exemplo')
        ->set('churchAddress.number', '100')
        ->set('churchAddress.complement', 'Sala 2')
        ->set('churchAddress.district', 'Centro')
        ->set('churchAddress.city', 'Sao Paulo')
        ->set('churchAddress.state', 'SP')
        ->call('submit')
        ->assertHasNoErrors();

    expect(Church::query()->count())->toBe(1);

    $this->assertDatabaseHas('churches', [
        'name' => 'Igreja Central',
        'pastor' => 'Joao Silva',
        'email' => 'contato@igreja.com',
        'phone' => '11999999999',
        'contact' => 'Maria Souza',
        'contact_phone' => '11888888888',
        'contact_email' => 'maria@igreja.com',
        'notes' => 'Comentario qualquer',
        'postal_code' => '01001-000',
        'street' => 'Rua Exemplo',
        'number' => '100',
        'complement' => 'Sala 2',
        'district' => 'Centro',
        'city' => 'Sao Paulo',
        'state' => 'SP',
    ]);
});
test('example', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});
