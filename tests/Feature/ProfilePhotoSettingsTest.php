<?php

use App\Livewire\Pages\App\Settings\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('uploads a profile photo from the profile settings page', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();

    $component = Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('profilePhotoUpload', UploadedFile::fake()->image('perfil.webp', 320, 320)->size(5120))
        ->call('updateProfilePhoto')
        ->assertHasNoErrors()
        ->assertSet('showPhotoModal', false)
        ->assertSet('savingProfilePhoto', false)
        ->assertDispatched('profile-photo-updated');

    $user->refresh();

    expect($user->profile_photo_path)->not->toBeNull()
        ->and((string) $user->profile_photo_path)->toContain('profile-photos/'.$user->id.'/')
        ->and($component->instance()->profilePhotoUrl())->not->toBeNull();

    Storage::disk('public')->assertExists((string) $user->profile_photo_path);
});

it('removes the current profile photo and falls back to initials', function (): void {
    Storage::fake('public');

    $existingPhotoPath = 'profile-photos/existing/perfil-antigo.webp';
    Storage::disk('public')->put($existingPhotoPath, 'fake-image-content');

    $user = User::factory()->create([
        'profile_photo_path' => $existingPhotoPath,
    ]);

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->call('removeProfilePhoto')
        ->assertHasNoErrors()
        ->assertSet('showPhotoModal', false)
        ->assertDispatched('profile-photo-removed');

    $user->refresh();

    expect($user->profile_photo_path)->toBeNull();

    Storage::disk('public')->assertMissing($existingPhotoPath);
});

it('resolves legacy profile photo paths stored with storage prefix or full url', function (): void {
    Storage::fake('public');

    Storage::disk('public')->put('profile-photos/legacy/perfil.webp', 'fake-image-content');

    $userWithStoragePrefix = User::factory()->create([
        'profile_photo_path' => 'storage/profile-photos/legacy/perfil.webp',
    ]);

    $userWithFullUrl = User::factory()->create([
        'profile_photo_path' => url('/storage/profile-photos/legacy/perfil.webp'),
    ]);

    expect($userWithStoragePrefix->profile_photo_url)->not->toBeNull()
        ->and($userWithStoragePrefix->normalizedProfilePhotoPath())->toBe('profile-photos/legacy/perfil.webp')
        ->and($userWithFullUrl->profile_photo_url)->not->toBeNull()
        ->and($userWithFullUrl->normalizedProfilePhotoPath())->toBe('profile-photos/legacy/perfil.webp');
});

it('validates the profile photo as soon as the file is selected', function (): void {
    Storage::fake('public');

    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(Profile::class)
        ->set('profilePhotoUpload', UploadedFile::fake()->create('perfil.pdf', 200, 'application/pdf'))
        ->assertHasErrors(['profilePhotoUpload' => 'image']);
});
