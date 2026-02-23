<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('redirects authenticated users that must change password to forced change screen', function (): void {
    $user = User::factory()->create([
        'must_change_password' => true,
    ]);

    $response = $this->actingAs($user)->get(route('app.start'));

    $response->assertRedirect(route('force-password-change.show'));
});

it('updates password and releases access after forced password change', function (): void {
    $user = User::factory()->create([
        'must_change_password' => true,
    ]);

    $this->actingAs($user)
        ->put(route('force-password-change.update'), [
            'password' => 'NovaSenha@2026',
            'password_confirmation' => 'NovaSenha@2026',
        ])
        ->assertRedirect(route('app.start'));

    $user->refresh();

    expect($user->must_change_password)->toBeFalse();
    expect(Hash::check('NovaSenha@2026', (string) $user->password))->toBeTrue();

    $this->actingAs($user)
        ->get(route('app.start'))
        ->assertOk();
});
