<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders translated role names in the sidebar dropdown', function (): void {
    $user = User::factory()->create();

    $roleIds = collect([
        'Board',
        'Director',
        'FieldWorker',
        'Teacher',
        'Facilitator',
        'Mentor',
        'Student',
    ])->map(fn (string $roleName): int => (int) Role::query()->firstOrCreate(['name' => $roleName])->id);

    $user->roles()->syncWithoutDetaching($roleIds->all());

    $response = $this
        ->actingAs($user)
        ->get(route('app.director.dashboard'));

    $response->assertOk();
    $response->assertSeeText('Membro do Board');
    $response->assertSeeText('Diretor Nacional');
    $response->assertSeeText('Professor');
    $response->assertSeeText('Facilitador');
    $response->assertSeeText('Missionário');
    $response->assertSeeText('Mentor');
    $response->assertSeeText('Aluno');
});
