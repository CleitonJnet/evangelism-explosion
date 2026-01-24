<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;

test('profile view page is displayed', function () {
    $this->actingAs($user = User::factory()->create());

    $this->get(route('app.profile'))
        ->assertOk()
        ->assertSee($user->name)
        ->assertSee('data-test="change-church"', false)
        ->assertSee(__('Two-Factor Auth'))
        ->assertSee(__('Appearance'))
        ->assertSee(__('Delete account'))
        ->assertDontSee(__('Configurações do usuário'));
});

test('profile view refreshes church details after update', function () {
    $user = User::factory()->create([
        'church_id' => null,
    ]);

    $church = \App\Models\Church::create([
        'name' => 'Igreja Renovada',
        'street' => 'Rua A',
        'number' => '10',
        'district' => 'Centro',
        'city' => 'Sao Paulo',
        'state' => 'SP',
    ]);

    $this->actingAs($user);

    $component = Volt::test('pages.app.settings.profile')
        ->assertSee(__('Sem igreja vinculada'));

    $user->forceFill(['church_id' => $church->id])->save();

    $component->call('refreshFromChurchLink')
        ->assertSee('Igreja Renovada');
});

test('user can update personal data from profile view', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Volt::test('pages.app.settings.profile')
        ->set('personal.name', 'Novo Nome')
        ->set('personal.email', 'novo-email@example.com')
        ->set('personal.phone', '11999999999')
        ->call('updatePersonal');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->name)->toBe('Novo Nome');
    expect($user->email)->toBe('novo-email@example.com');
    expect($user->email_verified_at)->toBeNull();
});

test('user can update address from profile view', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Volt::test('pages.app.settings.profile')
        ->set('address.street', 'Rua das Flores')
        ->set('address.number', '123')
        ->set('address.district', 'Centro')
        ->set('address.city', 'Sao Paulo')
        ->set('address.state', 'SP')
        ->set('address.postal_code', '01001000')
        ->call('updateAddress');

    $response->assertHasNoErrors();

    $user->refresh();

    expect($user->street)->toBe('Rua das Flores');
    expect($user->number)->toBe('123');
    expect($user->district)->toBe('Centro');
    expect($user->city)->toBe('Sao Paulo');
    expect($user->state)->toBe('SP');
    expect($user->getRawOriginal('postal_code'))->toBe('01001000');
});

test('user can update password from profile view', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = Volt::test('pages.app.settings.profile')
        ->set('current_password', 'Master@01')
        ->set('password', 'NovaSenha@01')
        ->set('password_confirmation', 'NovaSenha@01')
        ->call('updatePassword');

    $response->assertHasNoErrors();

    expect(Hash::check('NovaSenha@01', $user->fresh()->password))->toBeTrue();
});
