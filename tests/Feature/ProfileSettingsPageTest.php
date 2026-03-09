<?php

use App\Livewire\Pages\App\Settings\Profile;
use Illuminate\Support\Facades\Route;

it('registers the canonical profile route with the class-based livewire component', function (): void {
    $route = Route::getRoutes()->getByName('app.profile');

    expect($route)->not->toBeNull();
    expect($route?->uri())->toBe('settings/profile');
    expect($route?->getActionName())->toBe(Profile::class);
});

it('does not keep the legacy profile edit route registered', function (): void {
    expect(Route::has('app.profile.edit'))->toBeFalse();
});

it('keeps the profile backend actions on the class-based livewire component', function (): void {
    expect(method_exists(Profile::class, 'mount'))->toBeTrue();
    expect(method_exists(Profile::class, 'updatePersonal'))->toBeTrue();
    expect(method_exists(Profile::class, 'updateAddress'))->toBeTrue();
    expect(method_exists(Profile::class, 'updatePassword'))->toBeTrue();
    expect(method_exists(Profile::class, 'openChurchModal'))->toBeTrue();
});
