<?php

use App\Livewire\Shared\CreateChurchTempModal;
use App\Models\ChurchTemp;
use App\Models\User;
use Livewire\Livewire;

it('creates a pending church temp and links it to user', function (): void {
    $user = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(CreateChurchTempModal::class)
        ->set('churchTempName', 'Igreja Esperanca da Serra')
        ->set('churchTempPastor', 'Pr. Joao Souza')
        ->set('churchTempPostalCode', '12345000')
        ->set('churchTempStreet', 'Rua das Flores')
        ->set('churchTempNumber', '100')
        ->set('churchTempDistrict', 'Centro')
        ->set('churchTempCity', 'Recife')
        ->set('churchTempState', 'pe')
        ->set('churchTempPhone', '81999998888')
        ->set('churchTempEmail', 'igreja@example.com')
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();
    $churchTemp = ChurchTemp::query()->find($user->church_temp_id);

    expect($churchTemp)->not->toBeNull();
    expect($churchTemp->status)->toBe('pending');
    expect($churchTemp->normalized_name)->toBe('igreja esperanca da serra');
    expect($churchTemp->state)->toBe('PE');
    expect($user->church_id)->toBeNull();
});

it('reuses an existing pending church temp with same normalized name', function (): void {
    $existing = ChurchTemp::query()->create([
        'name' => 'Igreja Vida Nova',
        'pastor' => 'Pr. Paulo',
        'postal_code' => '11222333',
        'street' => 'Avenida Central',
        'number' => '50',
        'district' => 'Bela Vista',
        'city' => 'Curitiba',
        'state' => 'PR',
        'status' => 'pending',
        'normalized_name' => 'igreja vida nova',
    ]);

    $user = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => null,
    ]);

    $this->actingAs($user);

    Livewire::test(CreateChurchTempModal::class)
        ->set('churchTempName', '  IGREJA  VIDA  NÓVA  ')
        ->set('churchTempPastor', 'Pr. Novo Nome')
        ->set('churchTempPostalCode', '99888777')
        ->set('churchTempStreet', 'Rua A')
        ->set('churchTempNumber', '10')
        ->set('churchTempDistrict', 'Centro')
        ->set('churchTempCity', 'Curitiba')
        ->set('churchTempState', 'PR')
        ->call('save')
        ->assertHasNoErrors();

    $user->refresh();

    expect($user->church_temp_id)->toBe($existing->id);
    expect(ChurchTemp::query()->where('normalized_name', 'igreja vida nova')->count())->toBe(1);
});
