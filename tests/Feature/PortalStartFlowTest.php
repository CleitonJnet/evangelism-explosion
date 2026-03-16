<?php

use App\Models\Role;
use App\Models\User;
use App\Services\Portals\PortalSessionManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class);
});

function userWithPortalRoles(array $roleNames): User
{
    $user = User::factory()->create();
    $roleIds = collect($roleNames)
        ->map(fn (string $roleName): int => Role::query()->firstOrCreate(['name' => $roleName])->id);

    $user->roles()->syncWithoutDetaching($roleIds->all());

    return $user;
}

it('keeps the Fortify login redirect pointed at the authenticated start flow', function (): void {
    $user = userWithPortalRoles(['Teacher']);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'Master@01',
    ])->assertRedirect('/start');

    expect(auth()->check())->toBeTrue();
});

it('redirects directly to the only available portal from app start', function (): void {
    $user = userWithPortalRoles(['Teacher']);

    $this->actingAs($user)
        ->get(route('app.start'))
        ->assertRedirect(route('app.portal.base.dashboard'));

    $this->actingAs($user)
        ->get(route('app.portal.base.dashboard'))
        ->assertSessionHas(PortalSessionManager::CURRENT_PORTAL_SESSION_KEY, 'base')
        ->assertSessionHas(PortalSessionManager::LAST_PORTAL_SESSION_KEY, 'base');
});

it('shows the portal selection screen when the user can access more than one portal', function (): void {
    $user = userWithPortalRoles(['Director', 'Student']);

    $this->actingAs($user)
        ->get(route('app.start'))
        ->assertOk()
        ->assertSee('Escolha o portal de entrada')
        ->assertSee('Base e Treinamentos')
        ->assertSee('Staff / Governanca')
        ->assertSee('Aluno');
});

it('shows a safe empty state when the user has no available portal', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('app.start'))
        ->assertOk()
        ->assertSee('Nenhum portal esta disponivel para este usuario.');
});

it('stores the selected portal in session and redirects to its entry route', function (): void {
    $user = userWithPortalRoles(['Director', 'Student']);

    $this->actingAs($user)
        ->post(route('app.portal.select', ['portal' => 'student']))
        ->assertRedirect(route('app.portal.student.dashboard'))
        ->assertSessionHas(PortalSessionManager::CURRENT_PORTAL_SESSION_KEY, 'student')
        ->assertSessionHas(PortalSessionManager::LAST_PORTAL_SESSION_KEY, 'student');
});

it('exposes the current portal in the shared layout after entering a portal route', function (): void {
    $user = userWithPortalRoles(['Teacher']);

    $this->actingAs($user)
        ->get(route('app.portal.base.dashboard'))
        ->assertOk()
        ->assertSee('Portal atual')
        ->assertSee('Base e Treinamentos')
        ->assertSee('Trocar portal');
});
