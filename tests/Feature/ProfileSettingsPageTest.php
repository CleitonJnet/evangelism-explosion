<?php

use App\Livewire\Pages\App\Settings\Profile;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('registers the canonical profile route with the class-based livewire component', function (): void {
    $route = Route::getRoutes()->getByName('app.profile');
    $managedRoute = Route::getRoutes()->getByName('app.profile.show');

    expect($route)->not->toBeNull();
    expect($route?->uri())->toBe('settings/profile');
    expect($route?->getActionName())->toBe(Profile::class);
    expect($managedRoute)->not->toBeNull();
    expect($managedRoute?->uri())->toBe('settings/profile/{user}');
    expect($managedRoute?->getActionName())->toBe(Profile::class);
});

it('does not keep the legacy profile edit route registered', function (): void {
    expect(Route::has('app.profile.edit'))->toBeFalse();
});

it('keeps the profile backend actions on the class-based livewire component', function (): void {
    expect(method_exists(Profile::class, 'mount'))->toBeTrue();
    expect(method_exists(Profile::class, 'updatePersonal'))->toBeTrue();
    expect(method_exists(Profile::class, 'updateAddress'))->toBeTrue();
    expect(method_exists(Profile::class, 'updatePassword'))->toBeTrue();
    expect(method_exists(Profile::class, 'openPhotoModal'))->toBeTrue();
    expect(method_exists(Profile::class, 'updateProfilePhoto'))->toBeTrue();
    expect(method_exists(Profile::class, 'removeProfilePhoto'))->toBeTrue();
    expect(method_exists(Profile::class, 'openChurchModal'))->toBeTrue();
});

it('allows a director to open the existing profile page for another user', function (): void {
    $director = User::factory()->create();
    $teacher = User::factory()->create([
        'name' => 'Professor Perfil Existente',
        'email' => 'professor.perfil@example.com',
    ]);

    $role = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$role->id]);

    $response = $this
        ->actingAs($director)
        ->get(route('app.profile.show', $teacher));

    $response->assertOk();
    $response->assertSee('Perfil do Usuário');
    $response->assertSee('Professor Perfil Existente');
    $response->assertDontSee('profile-delete-account-modal');
    $response->assertDontSee('profile-password-modal');
    $response->assertSee('profile-delete-managed-user');
});

it('requires the director password to delete another user from the existing profile page', function (): void {
    $director = User::factory()->create([
        'password' => Hash::make('secret-123'),
    ]);
    $teacher = User::factory()->create([
        'email' => 'professor.excluir@example.com',
    ]);

    $role = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$role->id]);

    Livewire::actingAs($director)
        ->test(Profile::class, ['user' => $teacher])
        ->call('openDeleteModal')
        ->set('deletePassword', 'senha-incorreta')
        ->call('deleteProfile')
        ->assertHasErrors(['deletePassword']);

    expect(User::query()->whereKey($teacher->id)->exists())->toBeTrue();

    Livewire::actingAs($director)
        ->test(Profile::class, ['user' => $teacher])
        ->call('openDeleteModal')
        ->set('deletePassword', 'secret-123')
        ->call('deleteProfile')
        ->assertRedirect(route('app.director.dashboard'));

    expect(User::query()->whereKey($teacher->id)->exists())->toBeFalse();
});
