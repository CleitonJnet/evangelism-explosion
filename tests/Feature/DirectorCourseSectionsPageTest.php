<?php

use App\Models\Course;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createDirectorForCourseSectionsPage(): User
{
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    return $director;
}

it('renders a dedicated page for managing course sections', function (): void {
    $ministry = Ministry::query()->create([
        'name' => 'Evangelismo Eficaz',
        'initials' => 'EE',
        'color' => '#1d4ed8',
    ]);

    $course = Course::factory()->create([
        'ministry_id' => $ministry->id,
        'type' => 'Clínica',
        'name' => 'Clínica de Líderes',
        'initials' => 'CL',
        'color' => '#1d4ed8',
    ]);

    Section::factory()->create([
        'course_id' => $course->id,
        'name' => 'Unidade 1',
        'order' => 1,
        'duration' => '45 min',
        'devotional' => 'João 3',
    ]);

    Section::factory()->create([
        'course_id' => $course->id,
        'name' => 'Unidade 2',
        'order' => 2,
        'duration' => '1h',
        'devotional' => 'Atos 1',
    ]);

    $director = createDirectorForCourseSectionsPage();

    $this->actingAs($director)
        ->get(route('app.director.ministry.course.sections', ['ministry' => $ministry, 'course' => $course]))
        ->assertOk()
        ->assertSee('Unidades do curso')
        ->assertSee('Tabela de unidades')
        ->assertSee('Unidade 1')
        ->assertSee('Unidade 2')
        ->assertSee('Adicionar unidade');
});
