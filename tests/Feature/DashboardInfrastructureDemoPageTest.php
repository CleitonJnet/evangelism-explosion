<?php

use App\Livewire\Shared\Dashboard\InfrastructureDemoPage;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function dashboardUserWithRole(string $roleName): User
{
    $user = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => $roleName]);
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

it('defaults the infrastructure demo period to year', function (): void {
    $teacher = dashboardUserWithRole('Teacher');

    Livewire::actingAs($teacher)
        ->test(InfrastructureDemoPage::class, ['context' => 'teacher'])
        ->assertSet('period', 'year')
        ->assertSee('Anual')
        ->assertSee('Treinamentos no período');
});

it('accepts period changes and query string values', function (): void {
    $teacher = dashboardUserWithRole('Teacher');

    Livewire::actingAs($teacher)
        ->withQueryParams(['period' => 'quarter'])
        ->test(InfrastructureDemoPage::class, ['context' => 'teacher'])
        ->assertSet('period', 'quarter')
        ->set('period', 'semester')
        ->assertSet('period', 'semester');
});

it('renders the teacher and director infrastructure routes', function (): void {
    $teacher = dashboardUserWithRole('Teacher');
    $director = dashboardUserWithRole('Director');

    $this->actingAs($teacher)
        ->get(route('app.teacher.dashboard.infrastructure'))
        ->assertOk()
        ->assertSee('Infraestrutura de Dashboard')
        ->assertSee('Professor');

    $this->actingAs($director)
        ->get(route('app.director.dashboard.infrastructure', ['period' => 'semester']))
        ->assertOk()
        ->assertSee('Infraestrutura de Dashboard')
        ->assertSee('Diretor');
});
