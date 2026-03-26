<?php

use App\Models\Role;
use App\Models\User;
use App\Support\AccessProfile;
use Illuminate\Support\Facades\Session;

it('redirects authenticated users to the last access profile stored in the cookie', function (): void {
    $user = User::factory()->create();

    $roles = collect(['Teacher', 'Student'])
        ->map(fn (string $name): int => Role::query()->firstOrCreate(['name' => $name])->id)
        ->all();

    $user->roles()->sync($roles);

    $response = $this->actingAs($user)
        ->get(route('app.student.dashboard'));

    $response->assertOk();
    $response->assertCookie(AccessProfile::COOKIE_KEY);

    $cookieValue = collect($response->headers->getCookies())
        ->first(fn ($cookie): bool => $cookie->getName() === AccessProfile::COOKIE_KEY)
        ?->getValue();

    expect($cookieValue)->not->toBeNull();

    Session::invalidate();

    $this->actingAs($user)
        ->withUnencryptedCookie(AccessProfile::COOKIE_KEY, (string) $cookieValue)
        ->get(route('app.start'))
        ->assertRedirect(route('app.student.dashboard'));
});

it('shows the start screening when the remembered cookie profile is no longer valid for the user', function (): void {
    $user = User::factory()->create();

    $roles = collect(['Teacher', 'Student'])
        ->map(fn (string $name): int => Role::query()->firstOrCreate(['name' => $name])->id)
        ->all();

    $user->roles()->sync($roles);

    $response = $this->actingAs($user)
        ->withCookie(AccessProfile::COOKIE_KEY, json_encode([
            (string) $user->getKey() => 'Director',
        ]))
        ->get(route('app.start'));

    $response->assertOk();
    $response->assertSeeText('Professor Certificado');
    $response->assertSeeText('Aluno');
});
