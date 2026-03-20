<?php

use App\Livewire\Pages\App\Director\Ministry\View as MinistryDetailsView;
use App\Models\Course;
use App\Models\Ministry;
use App\Models\Role;
use App\Models\Section;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createDirectorForMinistryDetails(): User
{
    $director = User::factory()->create();
    $directorRole = Role::query()->firstOrCreate(['name' => 'Director']);
    $director->roles()->syncWithoutDetaching([$directorRole->id]);

    return $director;
}

it('separates leadership and implementation courses on ministry details page', function (): void {
    $ministry = Ministry::query()->create([
        'name' => 'Evangelismo Eficaz',
        'initials' => 'EE',
        'color' => '#0f172a',
        'description' => 'Trilha ministerial para capacitação e implantação local.',
    ]);

    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $facilitatorRole = Role::query()->firstOrCreate(['name' => 'Facilitator']);

    $leadershipCourse = Course::factory()->create([
        'ministry_id' => $ministry->id,
        'execution' => 0,
        'order' => 1,
        'is_accreditable' => true,
        'type' => 'Liderança',
        'name' => 'Clínica de Líderes',
        'price' => 120,
    ]);

    $implementationCourse = Course::factory()->create([
        'ministry_id' => $ministry->id,
        'execution' => 1,
        'order' => 2,
        'type' => 'Implementação',
        'name' => 'EE na Igreja Local',
        'slogan' => 'Aplicação prática',
        'price' => 80,
    ]);

    Section::factory()->count(2)->create(['course_id' => $leadershipCourse->id]);
    Section::factory()->create(['course_id' => $implementationCourse->id]);

    $certifiedTeacher = User::factory()->create(['name' => 'Professor Certificado']);
    $certifiedTeacher->roles()->syncWithoutDetaching([$teacherRole->id]);
    $leadershipCourse->teachers()->attach($certifiedTeacher->id, ['status' => 1]);

    $accreditedFacilitator = User::factory()->create(['name' => 'Facilitador Credenciado']);
    $accreditedFacilitator->roles()->syncWithoutDetaching([$facilitatorRole->id]);
    $implementationCourse->teachers()->attach($accreditedFacilitator->id, ['status' => 1]);

    $director = createDirectorForMinistryDetails();

    Livewire::actingAs($director)
        ->test(MinistryDetailsView::class, ['ministry' => $ministry])
        ->assertViewHas('leadershipTeachersCount', 1)
        ->assertViewHas('implementationFacilitatorsCount', 1)
        ->assertViewHas('teachersCount', 1)
        ->assertSee('Cursos do ministério')
        ->assertSee('Cursos de liderança')
        ->assertSee('Cursos de implementação')
        ->assertSee('Professores vinculados')
        ->assertSee('Facilitadores credenciados')
        ->assertSee('Clínica de Líderes')
        ->assertSee('EE na Igreja Local')
        ->assertSee('Credenciável')
        ->assertSee('R$ 120,00')
        ->assertSee('R$ 80,00');
});
