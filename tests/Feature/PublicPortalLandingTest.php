<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function createPortalUserForPublicLanding(array $roles): User
{
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $roleIds = collect($roles)
        ->map(fn (string $roleName): int => Role::query()->firstOrCreate(['name' => $roleName])->id)
        ->all();

    $user->roles()->syncWithoutDetaching($roleIds);

    return $user->fresh();
}

it('shows the portals entry on the public home and the public portals page', function (): void {
    $this->get(route('web.home'))
        ->assertSuccessful()
        ->assertSee('Três portais, uma plataforma mais clara')
        ->assertSee('Base e Treinamentos')
        ->assertSee('Staff / Governanca')
        ->assertSee('Aluno');

    $this->get(route('web.portals.index'))
        ->assertSuccessful()
        ->assertSee('Os 3 Portais da')
        ->assertSee('Plataforma Ministerial')
        ->assertSee('Ver detalhes');
});

it('shows an explanatory landing page for each portal', function (string $portal, string $headline): void {
    $this->get(route('web.portals.show', $portal))
        ->assertSuccessful()
        ->assertSee($headline)
        ->assertSee('Quem usa')
        ->assertSee('O que resolve');
})->with([
    ['base', 'Portal Base e Treinamentos'],
    ['staff', 'Portal Staff / Governanca'],
    ['student', 'Portal do Aluno'],
]);

it('redirects guest portal access to login preserving the portal context', function (): void {
    $this->get(route('web.portals.access', 'staff'))
        ->assertRedirect(route('login', ['portal' => 'staff']));
});

it('redirects authenticated users straight to the selected portal', function (): void {
    $user = createPortalUserForPublicLanding(['Director']);

    $this->actingAs($user)
        ->get(route('web.portals.access', 'staff'))
        ->assertRedirect(route('app.portal.staff.dashboard'));
});

it('redirects login to the selected portal when the authenticated user has access', function (): void {
    $user = createPortalUserForPublicLanding(['Student']);

    $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
        'portal' => 'student',
    ])->assertRedirect(route('app.portal.student.dashboard'));
});
