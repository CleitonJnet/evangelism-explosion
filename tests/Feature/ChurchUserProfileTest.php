<?php

use App\Livewire\Shared\ChurchUserProfile;
use App\Models\Church;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createChurchManager(string $roleName): User
{
    $user = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => $roleName]);
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

it('updates personal data, address and church from the church context profile', function (string $roleName): void {
    $manager = createChurchManager($roleName);

    $currentChurch = Church::factory()->create(['name' => 'Igreja Atual']);
    $newChurch = Church::factory()->create(['name' => 'Igreja Nova']);

    $profile = User::factory()->create([
        'name' => 'Usuario Original',
        'email' => 'usuario.original@example.com',
        'phone' => '11999999999',
        'city' => 'Campinas',
        'state' => 'SP',
        'church_id' => $currentChurch->id,
        'church_temp_id' => null,
    ]);

    Livewire::actingAs($manager)
        ->test(ChurchUserProfile::class, [
            'user' => $profile,
            'backUrl' => route($roleName === 'Teacher' ? 'app.teacher.churches.index' : 'app.director.church.index'),
            'backLabel' => 'Voltar',
        ])
        ->call('openPersonalModal')
        ->set('personal.name', 'Usuario Atualizado')
        ->set('personal.email', 'usuario.atualizado@example.com')
        ->set('personal.phone', '21988887777')
        ->set('personal.notes', 'Observacao atualizada')
        ->call('updatePersonal')
        ->assertDispatched('profile-personal-updated')
        ->call('openAddressModal')
        ->set('address.street', 'Rua Nova')
        ->set('address.number', '123')
        ->set('address.district', 'Centro')
        ->set('address.city', 'Recife')
        ->set('address.state', 'PE')
        ->call('updateAddress')
        ->assertDispatched('profile-address-updated')
        ->call('openChurchModal')
        ->set('selectedChurchId', $newChurch->id)
        ->call('updateChurch')
        ->assertDispatched('profile-church-updated');

    $profile->refresh();

    expect($profile->name)->toBe('Usuario Atualizado')
        ->and($profile->email)->toBe('usuario.atualizado@example.com')
        ->and($profile->phone)->toBe('(21) 98888-7777')
        ->and($profile->notes)->toBe('Observacao atualizada')
        ->and($profile->street)->toBe('Rua Nova')
        ->and($profile->number)->toBe('123')
        ->and($profile->district)->toBe('Centro')
        ->and($profile->city)->toBe('Recife')
        ->and($profile->state)->toBe('PE')
        ->and($profile->church_id)->toBe($newChurch->id)
        ->and($profile->church_temp_id)->toBeNull();
})->with([
    'teacher' => 'Teacher',
    'director' => 'Director',
]);

it('keeps the existing gender value when saving personal data without changing gender', function (): void {
    $manager = createChurchManager('Teacher');
    $profile = User::factory()->create([
        'name' => 'Usuario Com Genero',
        'email' => 'usuario.genero@example.com',
        'gender' => User::GENDER_FEMALE,
    ]);

    Livewire::actingAs($manager)
        ->test(ChurchUserProfile::class, [
            'user' => $profile,
            'backUrl' => route('app.teacher.churches.index'),
            'backLabel' => 'Voltar',
        ])
        ->call('openPersonalModal')
        ->assertSet('personal.gender', (string) User::GENDER_FEMALE)
        ->set('personal.name', 'Usuario Com Genero Atualizado')
        ->call('updatePersonal')
        ->assertHasNoErrors()
        ->assertDispatched('profile-personal-updated');

    $profile->refresh();

    expect($profile->name)->toBe('Usuario Com Genero Atualizado')
        ->and($profile->gender)->toBe(User::GENDER_FEMALE);
});

it('stores null birthdate when personal birthdate is cleared', function (): void {
    $manager = createChurchManager('Teacher');
    $profile = User::factory()->create([
        'birthdate' => '1990-05-10',
        'email' => 'usuario.nascimento@example.com',
    ]);

    Livewire::actingAs($manager)
        ->test(ChurchUserProfile::class, [
            'user' => $profile,
            'backUrl' => route('app.teacher.churches.index'),
            'backLabel' => 'Voltar',
        ])
        ->call('openPersonalModal')
        ->assertSet('personal.birthdate', '1990-05-10')
        ->set('personal.birthdate', '')
        ->call('updatePersonal')
        ->assertHasNoErrors()
        ->assertDispatched('profile-personal-updated');

    expect($profile->fresh()->birthdate)->toBeNull();
});
