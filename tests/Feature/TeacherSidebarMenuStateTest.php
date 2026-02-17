<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTeacherUser(): User
{
    $teacher = User::factory()->create();
    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);

    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

it('renders dashboard menu item as active and trainings as inactive on dashboard route', function () {
    $teacher = createTeacherUser();

    $response = $this
        ->actingAs($teacher)
        ->get(route('app.teacher.dashboard'));

    $response->assertOk();
    $response->assertSee('z-9999!', false);
    $response->assertSee('text-amber-200/90 hover:text-amber-100 border border-amber-200/30 bg-white/10', false);
    $response->assertSee('text-slate-200/90 hover:text-amber-100 border-0 bg-transparent', false);
    $response->assertSeeText('Dashboard');
    $response->assertSeeText('Treinamentos');
});

it('renders trainings menu item as active and dashboard as inactive on trainings route', function () {
    $teacher = createTeacherUser();

    $response = $this
        ->actingAs($teacher)
        ->get(route('app.teacher.trainings.index'));

    $response->assertOk();
    $response->assertSee('text-amber-200/90 hover:text-amber-100 border border-amber-200/30 bg-white/10', false);
    $response->assertSee('text-slate-200/90 hover:text-amber-100 border-0 bg-transparent', false);
    $response->assertSeeText('Dashboard');
    $response->assertSeeText('Treinamentos');
});
