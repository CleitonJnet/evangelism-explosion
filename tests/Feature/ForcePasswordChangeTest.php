<?php

use App\Models\Role;
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

it('shows translated role labels on the start screening page', function (): void {
    $user = User::factory()->create();

    $roles = collect(['Board', 'Director', 'FieldWorker', 'Teacher', 'Facilitator', 'Mentor', 'Student'])
        ->map(fn (string $name): int => Role::query()->firstOrCreate(['name' => $name])->id)
        ->all();

    $user->roles()->sync($roles);

    $response = $this->actingAs($user)->get(route('app.start'));

    $response->assertOk();
    $response->assertSeeText('Membro do Conselho');
    $response->assertSeeText('Diretor Nacional');
    $response->assertSeeText('Missionário de Campo');
    $response->assertSeeText('Professor Certificado');
    $response->assertSeeText('Professor Local');
    $response->assertSeeText('Mentor');
    $response->assertSeeText('Aluno');
});
